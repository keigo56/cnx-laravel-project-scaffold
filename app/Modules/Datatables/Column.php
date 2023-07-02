<?php

namespace App\Modules\Datatables;

use App\Modules\Datatables\Enums\Operations;

class Column
{

    private string $key = '';
    private string $label = '';

    private string $datatype = 'string';

    private bool $isVisible = true;

    private static array $operations = [
        'string' => [
            Operations::DOES_NOT_CONTAIN,
            Operations::CONTAINS,
            Operations::EQUALS,
            Operations::DOES_NOT_EQUAL,
            Operations::STARTS_WITH,
            Operations::ENDS_WITH,
            Operations::IS_NOT_IN,
            Operations::IS_IN,
            Operations::IS_BLANK,
            Operations::IS_NOT_BLANK,
        ],
        'numeric' => [
            Operations::EQUALS,
            Operations::DOES_NOT_EQUAL,
            Operations::GREATER_THAN,
            Operations::LESS_THAN,
            Operations::GREATER_THAN_EQUAL,
            Operations::LESS_THAN_EQUAL,
            Operations::IS_NOT_IN_BETWEEN,
            Operations::IS_IN_BETWEEN,
            Operations::IS_BLANK,
            Operations::IS_NOT_BLANK,
        ],
        'date' => [
            Operations::EQUALS,
            Operations::DOES_NOT_EQUAL,
            Operations::GREATER_THAN,
            Operations::LESS_THAN,
            Operations::GREATER_THAN_EQUAL,
            Operations::LESS_THAN_EQUAL,
            Operations::IS_NOT_IN_BETWEEN,
            Operations::IS_IN_BETWEEN,
            Operations::IS_BLANK,
            Operations::IS_NOT_BLANK,
        ],
        'boolean' => [
            Operations::EQUALS,
            Operations::DOES_NOT_EQUAL,
            Operations::IS_BLANK,
            Operations::IS_NOT_BLANK,
        ],
    ];

    public static function make(): Column
    {
        return new static();
    }

    public function key(string $key): Column
    {
        $this->key = $key;
        return $this;
    }

    public function label(string $label): Column
    {
        $this->label = $label;
        return $this;
    }

    public function visible(string $isVisible): Column
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    public function string() : Column
    {
        $this->datatype = 'string';
        return $this;
    }

    public function numeric() : Column
    {
        $this->datatype = 'numeric';
        return $this;
    }

    public function date() : Column
    {
        $this->datatype = 'date';
        return $this;
    }

    public function boolean() : Column
    {
        $this->datatype = 'boolean';
        return $this;
    }

    public function getDatatype() : string
    {
        return $this->datatype;
    }

    public function getKey() : string
    {
        return $this->key;
    }


    public function getLabel() : string
    {
        if($this->label === '') return str($this->key)->title()->toString();

        return $this->label;
    }

    public function getOperation(): array {
        return self::$operations[$this->datatype] ?? [];
    }

    public function getIsVisible() : bool
    {
        return $this->isVisible;
    }
}
