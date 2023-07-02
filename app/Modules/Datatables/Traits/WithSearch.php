<?php

namespace App\Modules\Datatables\Traits;

use App\Modules\Datatables\DataTable;

trait WithSearch
{

    protected string $searchTerm = '';

    /**
     * Applies a search filter to the query builder using the given search string.
     *
     * @param string|null $queryString The search string to use for filtering.
     *
     * @return WithSearch|DataTable
     */
    public function search(string|null $queryString) : self
    {
        if (!$queryString) {
            return $this;
        }

        $this->searchTerm = $queryString;

        $columns = $this->columns->getColumns();

        $this->queryBuilder->where(function ($query) use ($queryString, $columns) {
            foreach ($columns as $column) {
                $query->orWhere($column->getKey(), 'LIKE', "%$queryString%");
            }
        });

        return $this;
    }
}
