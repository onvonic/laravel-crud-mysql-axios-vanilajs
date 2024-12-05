<?php

namespace App\Http\Controllers\cont_app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Helpers\TelegramLogHelper;
use App\Helpers\FileLogHelper;

class UsersController extends Controller
{
    # USER
    public function view()
    {
        return view('page_app.users.users');
    }
    public function data(Request $request)
    {
        try {
            $id     = $request->input('id');
            $email  = $request->input('email');
            $search = $request->input('search');
            $status = $request->input('status');
            $roles  = $request->input('roles');
            $limit  = $request->input('limit', 10);

            $query = DB::table('users')
                ->select('id', 'name', 'email', 'roles', 'provider', 'status', 'last_login', 'photo', 'created_at', 'updated_at')
                ->orderBy('id', 'DESC');

            # Prioritize search by ID and Email if both exist
            if ($id && $email) {
                $data = $query->where('id', $id)->where('email', $email)->first();
            } elseif ($id) {
                $data = $query->where('id', $id)->first();
            } elseif ($email) {
                $data = $query->where('email', $email)->first();
            } else {
                # Apply search filters
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
                    });
                }
                # Filter by roles if provided
                if ($roles) {
                    $query->where('roles', $roles);
                }
                # Filter by status
                if ($status !== null) {
                    if ($status !== '') {
                        $query->where('status', $status);
                    }
                }
                $data = $query->limit($limit)->get();
            }

            # Process data to add extra information
            if ($data instanceof \Illuminate\Support\Collection) {
                $data->transform(function ($item) {
                    $item->last_login_relative = $item->last_login ? Carbon::parse($item->last_login)->diffForHumans() : 'Never logged in';
                    $item->password_updated    = $item->created_at === $item->updated_at ? 'Never updated' : 'Updated';
                    return $item;
                });
            } elseif ($data) {
                // For single data found
                $data = collect([$data])->transform(function ($item) {
                    $item->last_login_relative = $item->last_login ? Carbon::parse($item->last_login)->diffForHumans() : 'Never logged in';
                    $item->password_updated    = $item->created_at === $item->updated_at ? 'Never updated' : 'Updated';
                    return $item;
                })->first();
            }

            return response()->json([
                'status'  => true,
                'message' => 'User data retrieved',
                'count'   => $data instanceof \Illuminate\Support\Collection ? $data->count() : ($data ? 1 : 0),
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'view',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Error occurred',
                'error'   => 'Internal error. Check logs.'
            ]);
        }
    }
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string',
            'roles'    => 'required|string',
        ]);
        if ($request->hasFile('photo')) {
            $validator->after(function ($validator) use ($request) {
                $photoValidation = Validator::make($request->all(), [
                    'photo' => 'image|mimes:jpeg,png,jpg|max:2048',
                ]);
                if ($photoValidation->fails()) {
                    $validator->errors()->merge($photoValidation->errors());
                }
            });
        }
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoName = 'profile_' . now()->format('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $request->photo->getClientOriginalExtension();
                $photoPath = $request->photo->storeAs('public', $photoName);
                $photoUrl  = url(Storage::url($photoPath));
            }
            DB::table('users')->insert([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
                'roles'    => $request->roles,
                'status'   => "active",
                'photo'    => isset($photoUrl) ? $photoUrl : null
            ]);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Berhasil simpan data',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'insert',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|int',
            'name'     => 'required|string|max:255',
            'password' => 'nullable|string|max:20',
            'roles'    => 'required|string',
            'email'    => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($request->input('id')),
            ],
        ]);
        if ($request->hasFile('photo')) {
            $validator->after(function ($validator) use ($request) {
                $photoValidation = Validator::make($request->all(), [
                    'photo' => 'image|mimes:jpeg,png,jpg|max:2048',
                ]);
                if ($photoValidation->fails()) {
                    $validator->errors()->merge($photoValidation->errors());
                }
            });
        }
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'name'  => $request->name,
                'email' => $request->email,
                'roles' => $request->roles,
            ];
            // Update password jika ada
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }
            // Update photo jika ada
            if ($request->hasFile('photo')) {
                $photoName           = 'profile_' . now()->format('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $request->photo->getClientOriginalExtension();
                $photoPath           = $request->photo->storeAs('public', $photoName);
                $updateData['photo'] = url(Storage::url($photoPath));
            }
            // Update data di database
            DB::table('users')->where('id', $request->id)->update($updateData);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Berhasil update data',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'insert',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function updatestatus(Request $request)
    {
        DB::table('users')->where('id', $request->id)->update([
            'status' => $request->status,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Success updating status',
        ]);
    }
    public function delete(REQUEST $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        try {
            $id = $request->input('id');
            // Cek apakah id digunakan di tabel lain
            // $isReferenced = DB::table('another_table')->where('employee_email_id', $id)->exists();
            // if ($isReferenced) {
            //     return response()->json([
            //         'message' => 'Cannot delete data as it is referenced in another table',
            //     ], 400);
            // }
            DB::table('users')->where('id', $id)->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Berhasil simpan data',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'insert',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function soft_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        try {
            $id = $request->input('id');
            // Cek apakah id digunakan di tabel lain
            // $isReferenced = DB::table('another_table')->where('employee_email_id', $id)->exists();
            // if ($isReferenced) {
            //     return response()->json([
            //         'message' => 'Cannot delete data as it is referenced in another table',
            //     ], 400);
            // }
            DB::table('users')->where('id', $id)->update(['deleted_at' => now()]);
            return response()->json([
                'status'  => true,
                'message' => 'Berhasil delete data',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'insert',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function soft_delete_restore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        try {
            $id = $request->input('id');
            // Cek apakah id digunakan di tabel lain
            // $isReferenced = DB::table('another_table')->where('employee_email_id', $id)->exists();
            // if ($isReferenced) {
            //     return response()->json([
            //         'message' => 'Cannot delete data as it is referenced in another table',
            //     ], 400);
            // }
            DB::table('users')->where('id', $id)->update(['deleted_at' => null]);
            return response()->json([
                'status'  => true,
                'message' => 'Berhasil restore data',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'insert',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    # USER MODULES
    public function modules_view()
    {
        return view('page_app.users.users_modules');
    }
    public function modules_data(Request $request)
    {
        try {
            $id     = $request->input('id');
            $search = $request->input('search');
            $limit  = $request->input('limit', 100);

            $query = DB::table('user_modules')->orderBy('id', 'DESC');

            # Apply filters
            if ($id) {
                $data = $query->where('id', $id)->first();
            } else {
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('module_name', 'like', "%$search%")->orWhere('module_label', 'like', "%$search%");
                    });
                }
                $data = $query->limit($limit)->get();
            }
            return response()->json([
                'status'  => true,
                'message' => 'Master User Modules',
                'count'   => $data instanceof \Illuminate\Support\Collection ? $data->count() : ($data ? 1 : 0),
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_data',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_name' => 'required|max:100|unique:user_modules,module_name',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        DB::beginTransaction();
        try {
            DB::table('user_modules')->insert([
                'module_name'  => $request->module_name,
                'module_label' => $request->module_label
            ]);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully saved',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_insert',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'          => 'required|integer|exists:user_modules,id',
            'module_name' => 'required|string'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        DB::beginTransaction();
        try {
            DB::table('user_modules')->where('id', $request->id)->update([
                'module_name'  => $request->module_name,
                'module_label' => $request->module_label
            ]);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully updated',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_update',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_delete(REQUEST $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_modules,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        try {
            $id = $request->input('id');
            // Cek apakah id digunakan di tabel lain
            $isReferencedInUserModuleActions = DB::table('user_module_actions')->where('module_id', $id)->exists();
            $isReferencedInUserPermissions   = DB::table('user_permissions')->where('module_id', $id)->exists();
            if ($isReferencedInUserModuleActions || $isReferencedInUserPermissions) {
                return response()->json([
                    'message' => 'Cannot delete data as it is referenced in another table',
                ], 400);
            }
            DB::table('user_modules')->where('id', $id)->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Data permanently deleted',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_delete',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_soft_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_modules,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        try {
            $id = $request->input('id');
            DB::table('user_modules')->where('id', $id)
                ->update([
                    'deleted_at'      => now(),
                    'user_id_deleted' => Auth::user()->id,
                ]);
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully moved to trash',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_soft_delete',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_soft_delete_restore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_modules,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        try {
            $id = $request->input('id');
            DB::table('user_modules')->where('id', $id)
                ->update([
                    'deleted_at'      => null,
                    'user_id_deleted' => null,
                ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data successfully restored',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_soft_delete_restore',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    # USER MODULES
    public function modules_action_view()
    {
        return view('page_app.users.users_modules_action');
    }
    public function modules_action_data(Request $request)
    {
        try {
            $id     = $request->input('id');
            $search = $request->input('search');
            $limit  = $request->input('limit', 100);
            $module = $request->input('module');
            $action = $request->input('action');

            $query = DB::table('user_module_actions')
                ->join('user_modules', 'user_modules.id', '=', 'user_module_actions.module_id')
                ->select('user_module_actions.*', 'user_modules.module_name', 'user_modules.module_label')
                ->orderBy('user_module_actions.id', 'DESC');

            # Apply filters
            if ($id) {
                $data = $query->where('user_module_actions.id', $id)->first();
            } else {
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('user_modules.module_name', 'like', "%$search%")
                            ->orWhere('user_modules.module_label', 'like', "%$search%")
                            ->orWhere('user_module_actions.action_name', 'like', "%$search%");
                    });
                }
                # Filter berdasarkan roles jika ada
                if ($module) {
                    $query->where('module_id', $module);
                }
                # Filter berdasarkan action jika ada
                if ($action) {
                    $query->where('action_name', $action);
                }
                $data = $query->limit($limit)->get();
            }
            return response()->json([
                'status'  => true,
                'message' => 'Master User Modules Action',
                'count'   => $data instanceof \Illuminate\Support\Collection ? $data->count() : ($data ? 1 : 0),
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_data',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_action_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id'   => 'required|integer|exists:user_modules,id',
            'action_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        DB::beginTransaction();
        try {
            $existingAction = DB::table('user_module_actions')
                ->where('module_id', $request->module_id)
                ->where('action_name', $request->action_name)
                ->first();

            if ($existingAction) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Combination of module id and action name already exists.',
                ]);
            }
            DB::table('user_module_actions')->insert([
                'module_id'   => $request->module_id,
                'action_name' => $request->action_name
            ]);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully saved',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_insert',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_action_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'          => 'required|integer|exists:user_module_actions,id',
            'module_id'   => 'required|integer|exists:user_modules,id',
            'action_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        DB::beginTransaction();
        try {
            $existingAction = DB::table('user_module_actions')
                ->where('module_id', $request->module_id)
                ->where('action_name', $request->action_name)
                ->first();

            if ($existingAction) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Combination of module id and action name already exists.',
                ]);
            }
            DB::table('user_module_actions')->where('id', $request->id)->update([
                'module_id'   => $request->module_id,
                'action_name' => $request->action_name
            ]);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully updated',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_update',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_action_delete(REQUEST $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_module_actions,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        try {
            $id = $request->input('id');
            // Cek apakah id digunakan di tabel lain
            $isReferencedInUserPermissions = DB::table('user_permissions')->where('action_id', $id)->exists();
            if ($isReferencedInUserPermissions) {
                return response()->json([
                    'message' => 'Cannot delete data as it is referenced in another table',
                ], 400);
            }
            DB::table('user_module_actions')->where('id', $id)->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Data permanently deleted',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_delete',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_action_soft_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_module_actions,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        try {
            $id = $request->input('id');
            DB::table('user_module_actions')->where('id', $id)
                ->update([
                    'deleted_at'      => now(),
                    'user_id_deleted' => Auth::user()->id,
                ]);
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully moved to trash',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_soft_delete',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function modules_action_soft_delete_restore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_module_actions,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }

        try {
            $id = $request->input('id');
            DB::table('user_module_actions')->where('id', $id)
                ->update([
                    'deleted_at'      => null,
                    'user_id_deleted' => null,
                ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data successfully restored',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_soft_delete_restore',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    # USER MODULES
    public function permission_view()
    {
        return view('page_app.users.users_permission');
    }
    public function permission_data(Request $request)
    {
        try {
            $id     = $request->input('id');
            $email  = $request->input('email');
            $search = $request->input('search');
            $status = $request->input('status');
            $roles  = $request->input('roles');
            $limit  = $request->input('limit', 10);
            $query  = DB::table('users')->select('id', 'name', 'email', 'roles', 'provider', 'status', 'last_login', 'created_at', 'updated_at')->orderBy('id', 'DESC');
            # Prioritaskan pencarian berdasarkan ID dan Email jika keduanya ada
            if ($id && $email) {
                $data = $query->where('id', $id)->where('email', $email)->first();
            } elseif ($id) {
                $data = $query->where('id', $id)->first();
            } elseif ($email) {
                $data = $query->where('email', $email)->first();
            } else {
                # Filter berdasarkan parameter pencarian
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
                    });
                }
                # Filter berdasarkan roles jika ada
                if ($roles) {
                    $query->where('roles', $roles);
                }
                # Filter berdasarkan status
                if ($status !== null) {
                    if ($status === '') {
                        # Tidak memfilter berdasarkan status
                    } else {
                        $query->where('status', $status);
                    }
                }
                $data = $query->limit($limit)->get();
            }
            return response()->json([
                'status'  => true,
                'message' => 'data users',
                'count'   => $data instanceof \Illuminate\Support\Collection ? $data->count() : ($data ? 1 : 0),
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'view',
                'module' => 'users',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
    public function permission_set(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id'   => 'required|integer|exists:user_modules,id',
            'action_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'error'   => $errors
            ]);
        }
        DB::beginTransaction();
        try {
            $existingAction = DB::table('user_module_actions')
                ->where('module_id', $request->module_id)
                ->where('action_name', $request->action_name)
                ->first();

            if ($existingAction) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Combination of module id and action name already exists.',
                ]);
            }
            DB::table('user_module_actions')->insert([
                'module_id'   => $request->module_id,
                'action_name' => $request->action_name
            ]);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Data successfully saved',
            ]);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'method' => 'modules_action_insert',
                'module' => 'UsersController',
                'data'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine()
                ]
            ];
            FileLogHelper::sendLogMessage($data);
            TelegramLogHelper::sendLogMessage($data);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to view data',
                'error'   => 'An internal error occurred. Check the log file: '
            ]);
        }
    }
}
