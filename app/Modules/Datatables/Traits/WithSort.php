<?php

namespace App\Modules\Datatables\Traits;

use Exception;
use Illuminate\Support\Collection;

trait WithSort
{

    protected string $sortColumn = '';
    protected string $sortDirection = '';


    /**
     * @throws Exception
     */
    public function sortBy(string|null $sortColumn, string|null $sortDirection) : self
    {

        if(!isset($sortColumn)) {
            return $this;
        }

        if(!isset($sortDirection)) {
            $sortDirection = 'asc';
        }

        $this->validateSortColumn($sortColumn);
        $this->validateSortDirection($sortDirection);

        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;

        $this->queryBuilder->orderBy($sortColumn, $sortDirection);

        return $this;
    }

    /**
     * @throws Exception
     */
    private function validateSortColumn(string $sortColumn): void
    {
        $valid_columns =
            Collection::make($this->columns->toArray())
                ->pluck('key')
                ->toArray();

        if(!in_array($sortColumn, $valid_columns, true)){
            throw new Exception('Invalid Sort Column');
        }
    }

    /**
     * @throws Exception
     */
    private function validateSortDirection(string $sortDirection): void
    {
        $valid_directions = ['asc', 'desc'];
        if(!in_array($sortDirection, $valid_directions)){
            throw new Exception('Invalid Sort Direction');
        }
    }
}
