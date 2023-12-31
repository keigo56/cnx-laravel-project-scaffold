<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Modules\Datatables\Column;
use App\Modules\Datatables\DataTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private DataTable $datatable;

    public function __construct()
    {
        $this->datatable =  DataTable::init()
            ->query(
                Permission::query()
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
                    ->label('Permission Name')
                    ->string()
                ,
            ])
        ;
    }

    public function dataset(Request $request)
    {
        try {

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
                ->log('User exported permissions.csv');

            $filePath = $this->datatable
                ->filter($request->input('filters'))
                ->search($request->input('search'))
                ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                ->exportName('permissions')
                ->export();

            return response()
                ->download(Storage::path($filePath))
                ->deleteFileAfterSend();

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
