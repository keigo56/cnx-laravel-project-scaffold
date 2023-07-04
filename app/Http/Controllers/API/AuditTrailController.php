<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Modules\Datatables\Column;
use App\Modules\Datatables\DataTable;
use App\Modules\Datatables\Row;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

class AuditTrailController extends Controller
{
    private DataTable $datatable;

    public function __construct()
    {
        $this->datatable =  DataTable::init()
            ->query(
                DB::table(Activity::query()
                    ->select(
                        DB::raw('activity_log.id as activity_log_id'),
                        'activity_log.description',
                        'activity_log.causer_id',
                        DB::raw('activity_log.created_at as activity_log_created_at'),
                        'users.email'
                    )
                    ->join('users', 'users.id', '=', 'activity_log.causer_id'))
            )
            ->columns([
                Column::make()
                    ->key('activity_log_id')
                    ->label('ID')
                    ->numeric()
                    ->visible(false)
                ,
                Column::make()
                    ->key('email')
                    ->label('User Email')
                    ->string()
                ,
                Column::make()
                    ->key('description')
                    ->label('Activity')
                    ->string()
                ,
                Column::make()
                    ->key('activity_log_created_at')
                    ->label('Performed At')
                    ->date()
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
                    ->mapRow(function(Row $row){
                        $row->setValue(
                            'activity_log_created_at',
                            Carbon::parse($row->getValue('activity_log_created_at'))
                                ->timezone('Asia/Manila')
                                ->format('Y-m-d h:i A')
                        );
                    })
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
                ->log('User exported logs.csv');

            $this->datatable
                ->filter($request->input('filters'))
                ->search($request->input('search'))
                ->sortBy($request->input('sort_by'), $request->input('sort_direction'))
                ->exportName('logs')
                ->export();

        }catch (Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
