<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Datatables\Column;
use App\Modules\Datatables\DataTable;
use App\Modules\Datatables\Row;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private DataTable $datatable;

    public function __construct()
    {
        $this->datatable =  DataTable::init()
            ->query(
                User::query()
                    ->select(
                        'id',
                        'name',
                        'email',
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
                    ->label('Name')
                    ->string()
                ,
                Column::make()
                    ->key('email')
                    ->label('Email')
                    ->string()
                ,
            ])
        ;
    }

    public function dataset(Request $request)
    {
        try {

            activity()
                ->log('User viewed users datatable');

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
                ->log('User exported users.csv');

            $this->datatable
                ->filter($request->input('filters'))
                ->search($request->input('search'))
                ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                ->exportName('users')
                ->export();

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
