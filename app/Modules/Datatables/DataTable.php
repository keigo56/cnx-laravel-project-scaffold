<?php

namespace App\Modules\Datatables;

use App\Modules\Datatables\Traits\WithColumnDistinct;
use App\Modules\Datatables\Traits\WithExport;
use App\Modules\Datatables\Traits\WithFilter;
use App\Modules\Datatables\Traits\WithMapRow;
use App\Modules\Datatables\Traits\WithSearch;
use App\Modules\Datatables\Traits\WithSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DataTable
{

    use WithFilter, WithSearch, WithSort, WithColumnDistinct, WithExport, WithMapRow;

    private Builder|\Illuminate\Database\Query\Builder $queryBuilder;
    private LengthAwarePaginator|Collection|\Illuminate\Support\Collection|array $rows;
    private Columns $columns;

    public static function init(): DataTable
    {
        return new static();
    }

    public function columns(array $columns): DataTable
    {
        $this->columns = Columns::make()->setColumns($columns);
        return $this;
    }

    public function query(
        Builder|\Illuminate\Database\Query\Builder $builder
    ): DataTable
    {
        $this->queryBuilder = $builder;
        return $this;
    }

    public function get(): array
    {
        $this->rows = $this->queryBuilder->get();

        if($this->willMapRow){
            $this->mapRows();
        }

        return $this->getDatatableData();
    }

    public function paginate(int $pageSize = 15) : array
    {
        $this->rows = $this->queryBuilder->paginate($pageSize);

        if($this->willMapRow){
            $this->mapRows();
        }

        return $this->getDatatableData();
    }

    /**
     * Get the data needed for the DataTable.
     *
     * @return array
     */
    private function getDatatableData(): array
    {
        $queryParams = [];

        // Add search term to query params if it exists
        if ($this->searchTerm !== '') {
            $queryParams['search'] = $this->searchTerm;
        }

        // Add sort column and direction to query params if they both exist
        if (!empty($this->sortColumn) && !empty($this->sortDirection)) {
            $queryParams['sort'] = [
                'column' => $this->sortColumn,
                'direction' => $this->sortDirection
            ];
        }

        // Add filters to query params if they exist
        if (!empty($this->filters)) {
            $queryParams['filters'] = collect($this->filters)->map(fn($filter) => $filter->toArray());
        }

        // Build the DataTable data array
        return [
            'columns' => $this->columns->toArray(),
            'rows' => $this->rows,
            'config' => [
                'paginated' => true,
            ],
            'query_params' => $queryParams
        ];
    }
}
