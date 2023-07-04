<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Modules\Datatables\Column;
use App\Modules\Datatables\DataTable;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private DataTable $datatable;

    public function __construct()
    {
        $this->datatable =  DataTable::init()
            ->query(
                Role::query()
                    ->select(
                        'id',
                        'name',
                    )
            )
            ->columns([
                Column::make()
                    ->key('id')
                    ->label('ID')
                    ->numeric()
                    ->visible(false)
                ,
                Column::make()
                    ->key('name')
                    ->label('Role Name')
                    ->string()
                ,
            ])
        ;
    }

    public function dataset(Request $request)
    {
        try {

            activity()
                ->log('User viewed roles datatable');

            $datatable =
                $this->datatable
                    ->filter($request->input('filters'))
                    ->search($request->input('search'))
                    ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                    ->paginate()
            ;

            return response()->json([
                'datatable' => $datatable
            ]);

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function column_distinct_values(Request $request)
    {
        $request->validate([
            'column' => ['required', 'string']
        ]);

        try {

            $distinct_values =
                $this->datatable
                    ->filter($request->input('filters'))
                    ->search($request->input('search'))
                    ->distinct($request->input('column'))
            ;

            return response()->json([
                'column' => $request->input('column'),
                'distinct_values' => $distinct_values
            ]);

        }catch (Exception $exception){
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
                ->log('User exported roles.csv');

            $this->datatable
                ->filter($request->input('filters'))
                ->search($request->input('search'))
                ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                ->exportName('roles')
                ->export();

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function getPermissions(){

        try {

            $permissions = Permission::query()
                        ->select('id','name')
                        ->get();

            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function getRolePermissions(Role $role){

        try {

            $permissions = $role
                ->getAllPermissions()
                ->map(function($role){
                    return $role->id;
                });

            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function add(Request $request){
        $request->validate([
            'name' => ['required', 'min:3', 'unique:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'exists:permissions,id']
        ]);

        try {

            $role = Role::query()
            ->create([
                'name' => $request->input('name'),
                'guard_name' => 'web'
            ]);

            $permissions = $request->input('permissions');

            $role->syncPermissions($permissions);

            activity()
                ->performedOn($role)
                ->log('User added role');

            return response()->json([
                'success' => true,
                'message' => 'Role added successfully'
            ]);

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request){
        $request->validate([
            'id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'min:3', 'unique:roles,name,' . $request->input('id')],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'exists:permissions,id']
        ]);

        try {

            $role_id = $request->input('id');
            $role = Role::query()->where('id', $role_id)->first();
            $role->update([
                'name' => $request->input('name')
            ]);

            $permissions = $request->input('permissions');
            $role->syncPermissions($permissions);

            activity()
                ->performedOn($role)
                ->log('User updated role');

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully'
            ]);

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request){
        $request->validate([
            'id' => ['required', 'exists:roles,id']
        ]);

        try {

            $role_id = $request->input('id');
            $role = Role::query()->where('id', $role_id)->first();
            $role->delete();

            activity()
                ->performedOn($role)
                ->log('User deleted role');

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
