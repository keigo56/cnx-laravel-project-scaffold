<?php

namespace App\Modules\Datatables;

class Filter
{

    private string $column;
    private string $operation;
    private string|array|null $value;

    public static function make(): Filter
    {
        return new static();
    }

    public function column(string $column): Filter
    {
        $this->column = $column;
        return $this;
    }

    public function operation(string $operation): Filter
    {
        $this->operation = $operation;
        return $this;
    }

    public function value(string|array|null $value): Filter
    {
        $this->value = $value;
        return $this;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getValue(): string | array | null
    {
        return $this->value;
    }

    public function toArray() : array
    {
        return [
            "column" => $this->column,
            "operation" => $this->operation,
            "value" => $this->value
        ];
    }
}
