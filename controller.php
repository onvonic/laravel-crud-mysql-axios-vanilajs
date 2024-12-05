<?php

namespace App\Http\Controllers\cont_app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Helpers\TelegramLogHelper;
use App\Helpers\FileLogHelper;

class UsersController extends Controller
{
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
}
