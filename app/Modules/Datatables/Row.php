<?php

namespace App\Modules\Datatables;

class Row
{
    private array $row = [];

    public function setRowValue(array $row): void
    {
        $this->row = $row;
    }

    public function setValue(string $column, mixed $value): void
    {
        $this->row[$column] = $value;
    }

    public function getValue(string $column)
    {
        return $this->row[$column];
    }


    public function getRow(): array
    {
        return $this->row;
    }
}
