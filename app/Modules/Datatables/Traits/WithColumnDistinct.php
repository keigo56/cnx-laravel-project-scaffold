<?php

namespace App\Modules\Datatables\Traits;

use Exception;

trait WithColumnDistinct
{
    /**
     * @throws Exception
     */
    public function distinct(string|null $column, int $perPage = 15): array
    {
        $this->validateDistinctColumn($column);

        $paginator =  $this->queryBuilder
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->paginate($perPage);

        $formattedValues = collect($paginator->items())
            ->pluck($column)
            ->toArray();

        return [
            'paginator' => $paginator,
            'values' => $formattedValues
        ];
    }

    /**
     * @throws Exception
     */
    private function validateDistinctColumn(string|null $column): void
    {
        if(is_null($column)){
            throw new Exception("Column cannot be null");
        }

        if(!$this->columns->exists($column)){
            throw new Exception("Column $column not found");
        }
    }
}
