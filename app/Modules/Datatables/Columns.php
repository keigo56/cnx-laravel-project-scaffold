<?php

namespace App\Modules\Datatables;

use Exception;
use InvalidArgumentException;

class Columns
{

    private array $columns = [];

    public static function make(): Columns
    {
        return new static();
    }

    public function setColumns(array $columns): Columns
    {
        foreach ($columns as $value) {
            if (!$value instanceof Column) {
                throw new InvalidArgumentException('Invalid argument: expected Column');
            }
        }

        foreach ($columns as $column){
            /** @var Column $column */
            $this->columns[$column->getKey()] = $column;
        }

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function toArray(): array
    {
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $this->parseColumn($column);
        }
        return $columns;
    }

    private function parseColumn(Column $column): array
    {
        return [
            'key' => $column->getKey(),
            'label' => $column->getLabel(),
            'datatype' => $column->getDatatype(),
            'operations' => $column->getOperation(),
            'visible' => $column->getIsVisible()
        ];
    }

    public function exists(string $searchColumn) : bool
    {
        foreach ($this->columns as $column){
            /** @var Column $column */
            if($searchColumn === $column->getKey()){
                return true;
            }
        }

        return false;
    }


    /**
     * @throws Exception
     */
    public function getColumn(string $columnKey): Column
    {
        if(!$this->exists($columnKey)){
            throw new Exception("No column $columnKey found");
        }

        return $this->columns[$columnKey];
    }
}
