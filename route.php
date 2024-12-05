use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\cont_app\UsersController;

Route::get('/users/modules/action', [UsersController::class, 'modules_action_view'])->name('app.users.modules.action.view');
Route::get('/users/modules/action/data', [UsersController::class, 'modules_action_data'])->name('app.users.modules.action.data');
Route::post('/users/modules/action/insert', [UsersController::class, 'modules_action_insert'])->name('app.users.modules.action.insert');
Route::post('/users/modules/action/update', [UsersController::class, 'modules_action_update'])->name('app.users.modules.action.update');
Route::post('/users/modules/action/delete', [UsersController::class, 'modules_action_delete'])->name('app.users.modules.action.delete');
