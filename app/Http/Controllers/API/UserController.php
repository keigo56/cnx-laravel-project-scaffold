<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Modules\Datatables\Column;
use App\Modules\Datatables\DataTable;
use App\Rules\SQLInputValidation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private DataTable $datatable;

    public function __construct()
    {
        $this->datatable = DataTable::init()
            ->query(
                User::query()
                    ->select(
                        DB::raw('users.id as user_id'),
                        'employees.workday_id',
                        'users.email',
                        'employees.name',
                        DB::raw('roles.name as role_name'),
                        DB::raw('roles.id as role_id'),
                    )
                    ->leftJoin('employees', 'employees.EmailAddress', '=', 'users.email') // JOIN EMPLOYEES GET MORE DATA
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', '=', 'App\Models\User')
                    ->whereNotIn('roles.name', ['passenger', 'driver'])
            )
            ->columns([
                Column::make()
                    ->key('user_id')
                    ->label('ID')
                    ->numeric()
                    ->visible(false),
                Column::make()
                    ->key('workday_id')
                    ->label('Workday ID')
                    ->string(),
                Column::make()
                    ->key('email')
                    ->label('Email')
                    ->string(),
                Column::make()
                    ->key('name')
                    ->label('Name')
                    ->string(),
                Column::make()
                    ->key('role_name')
                    ->label('Role Name')
                    ->string(),
                Column::make()
                    ->key('role_id')
                    ->label('role_id')
                    ->string()
                    ->visible(false),
            ]);
    }

    public function dataset(Request $request)
    {
        try {

            $datatable =
                $this->datatable
                    ->filter($request->input('filters'))
                    ->search($request->input('search'))
                    ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                    ->paginate();

            return response()->json([
                'datatable' => $datatable,
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function column_distinct_values(Request $request)
    {
        $request->validate([
            'column' => ['required', 'string'],
        ]);

        try {

            $distinct_values =
                $this->datatable
                    ->filter($request->input('filters'))
                    ->search($request->input('search'))
                    ->distinct($request->input('column'));

            return response()->json([
                'column' => $request->input('column'),
                'distinct_values' => $distinct_values,
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {

            activity()
                ->log('User exported users.csv');

            $filePath = $this->datatable
                ->filter($request->input('filters'))
                ->search($request->input('search'))
                ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                ->exportName('users')
                ->export();

            return response()
                ->download(Storage::path($filePath))
                ->deleteFileAfterSend();

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function search_employee(Request $request)
    {
        try {

            $employeeSearch = trim($request->input('query'));

            $employees = Employee::query()
                ->select('employees.name', DB::raw('employees.EmailAddress as email'))
                ->where('employees.EmployeeStatus', 'Active')
                ->whereNotNull('employees.EmailAddress')
                ->where('employees.EmailAddress', '!=', '')
                ->when(! empty($employeeSearch), function ($query) use ($employeeSearch) {
                    $query->where(function ($subQuery) use ($employeeSearch) {
                        $subQuery->where('employees.EmailAddress', 'LIKE', "%{$employeeSearch}%")
                            ->orWhere('employees.name', 'LIKE', "%{$employeeSearch}%")
                            ->orWhere('employees.workday_id', 'LIKE', "%{$employeeSearch}%");
                    });
                })
                ->orderBy('employees.name')
                ->limit(15)
                ->pluck('email')
                ->map(fn ($email) => ['value' => $email, 'label' => $email]);

            return response()->json([
                'result' => $employees,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function roles()
    {

        try {
            $roles = Role::query()
                ->select('roles.id', 'roles.name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'roles' => $roles,
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => ['required', 'exists:roles,id', new SQLInputValidation],
            'email' => ['required', 'email', 'exists:employees,EmailAddress', new SQLInputValidation],
        ]);

        try {

            $email = $request->input('email');
            $role_id = $request->input('role_id');

            $user = User::query()
                ->updateOrCreate([
                    'email' => $email,
                ],
                    [
                        'name' => $email,
                        'email' => $email,
                    ]);

            if ($user->roles->count() > 0) {
                return response()->json([
                    'errors' => [
                        'email' => ['The provided email already has role assigned'],
                    ],
                ], 422);
            }

            $user->syncRoles($role_id);

            activity()
                ->performedOn($user)
                ->log('User assigned role');

            return response()->json([
                'success' => true,
                'message' => 'User Role assigned',
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {

        $request->validate([
            'role_id' => ['required', 'exists:roles,id', new SQLInputValidation],
            'email' => ['required', 'email', 'exists:employees,EmailAddress', new SQLInputValidation],
        ]);

        try {

            $email = $request->input('email');
            $role_id = $request->input('role_id');

            if ($email === Auth::user()->getAttribute('email')) {
                return response()->json([
                    'errors' => [
                        'email' => ['Cannot update own record'],
                    ],
                ], 422);
            }

            $user = User::query()
                ->updateOrCreate([
                    'email' => $email,
                ],
                    [
                        'email' => $email,
                        'password' => Hash::make('password'),
                    ]);

            $user->syncRoles($role_id);

            activity()
                ->performedOn($user)
                ->log('User updated role');

            return response()->json([
                'success' => true,
                'message' => 'User Role updated',
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email', new SQLInputValidation],
        ]);

        try {
            $email = $request->input('email');

            $user = User::query()
                ->where('email', $email)
                ->first();

            if ($user->getAttribute('id') === Auth::user()->getAttribute('id')) {
                return response()->json([
                    'errors' => [
                        'email' => ['Cannot delete own record'],
                    ],
                ], 422);
            }

            $user->syncRoles([]);

            activity()
                ->performedOn($user)
                ->log('User removed role');

            return response()->json([
                'success' => true,
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function user()
    {
        try {

            $user = User::query()
                ->select([
                    'users.id',
                    'employees.name',
                    'employees.EmailAddress as email',
                    'employees.workday_id',
                    'employees.Position as position',
                ])
                ->where('id', auth()->user()->getAttribute('id'))
                ->leftJoin('employees', 'employees.EmailAddress', '=', 'users.email')
                ->first();

            $permissions = $user
                ->getPermissionsViaRoles()
                ->map(function ($permission) {
                    return $permission['name'];
                });

            return response()->json([
                'success' => true,
                'user' => $user->only('name', 'email', 'workday_id', 'position'),
                'permissions' => $permissions,
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
