<?php

namespace App\Modules\Datatables\Traits;

use App\Modules\Datatables\Enums\Operations;
use App\Modules\Datatables\Filter;
use Exception;

trait WithFilter
{

    protected array $filters;


    /**
     * @throws Exception
     */
    public function filter(array|null $filters): self
    {
        if(is_null($filters)){
            return $this;
        }

        $this->parseFilters($filters);
        $this->filterQueryBuilder();

        return $this;
    }

    /**
     * @throws Exception
     */
    private function parseFilters(array $filters) : void
    {
        foreach ($filters as $filter){

            if( !array_key_exists('column', $filter) ||
                !array_key_exists('operation', $filter) ||
                !array_key_exists('value', $filter)
            ){
                throw new Exception('Invalid filter array given. The filter array requires column, operation and value key');
            }

            $column = $filter['column'];
            $operation = $filter['operation'];
            $value = $filter['value'];

            $this->validateColumn($column);
            $this->validateOperation($column, $operation);
            $this->validateValue($operation, $value);

            $this->filters[] = Filter::make()
                ->column($column)
                ->operation($operation)
                ->value($value);
        }
    }


    private function filterQueryBuilder(): void
    {
        $this->queryBuilder->where(function($query){
            foreach ($this->filters as $filter){
                /** @var Filter $filter */
                switch ($filter->getOperation()) {
                    case OPERATIONS::DOES_NOT_CONTAIN:
                        $query->where($filter->getColumn(), 'NOT LIKE', "%{$filter->getValue()}%");
                        break;
                    case OPERATIONS::CONTAINS:
                        $query->where($filter->getColumn(), 'LIKE', "%{$filter->getValue()}%");
                        break;
                    case OPERATIONS::EQUALS:
                        $query->where($filter->getColumn(), '=', $filter->getValue());
                        break;
                    case OPERATIONS::DOES_NOT_EQUAL:
                        $query->where($filter->getColumn(), '!=', $filter->getValue());
                        break;
                    case OPERATIONS::GREATER_THAN:
                        $query->where($filter->getColumn(), '>', $filter->getValue());
                        break;
                    case OPERATIONS::LESS_THAN:
                        $query->where($filter->getColumn(), '<', $filter->getValue());
                        break;
                    case OPERATIONS::GREATER_THAN_EQUAL:
                        $query->where($filter->getColumn(), '>=', $filter->getValue());
                        break;
                    case OPERATIONS::LESS_THAN_EQUAL:
                        $query->where($filter->getColumn(), '<=', $filter->getValue());
                        break;
                    case OPERATIONS::STARTS_WITH:
                        $query->where($filter->getColumn(), 'LIKE', "{$filter->getValue()}%");
                        break;
                    case OPERATIONS::ENDS_WITH:
                        $query->where($filter->getColumn(), 'LIKE', "%{$filter->getValue()}");
                        break;
                    case OPERATIONS::IS_NOT_IN:
                        $query->whereNotIn($filter->getColumn(), $filter->getValue());
                        break;
                    case OPERATIONS::IS_IN:
                        $query->whereIn($filter->getColumn(), $filter->getValue());
                        break;
                    case OPERATIONS::IS_IN_BETWEEN:
                        $query->whereBetween($filter->getColumn(), $filter->getValue());
                        break;
                    case OPERATIONS::IS_NOT_IN_BETWEEN:
                        $query->whereNotBetween($filter->getColumn(), $filter->getValue());
                        break;
                    case OPERATIONS::IS_BLANK:
                        $query->whereNull($filter->getColumn());
                        break;
                    case OPERATIONS::IS_NOT_BLANK:
                        $query->whereNotNull($filter->getColumn());
                        break;
                }

            }
        });
    }

    /**
     * @throws Exception
     */
    private function validateColumn(string|null $column): void
    {
        if(!isset($column)){
            throw new Exception("Column cannot be empty");
        }

        if(!$this->columns->exists($column)){
            throw new Exception("Column $column not found");
        }
    }

    /**
     * @throws Exception
     */
    private function validateOperation(string|null $column, string|null $operation): void
    {
        if(!isset($operation)){
            throw new Exception("Operation cannot be empty");
        }

        if(!in_array($operation, Operations::get())){
            throw new Exception("Operation $operation is not valid");
        }

        $columnObject = $this->columns->getColumn($column);

        if(!in_array($operation, $columnObject->getOperation())){
            throw new Exception("Operation $operation is not allowed for column $column with datatype of {$columnObject->getDatatype()}");
        }

    }

    /**
     * Validates the value based on the operation.
     *
     * @param string $operation The operation to perform.
     * @param string|array|null $value The value to be validated.
     * @throws Exception If the value is empty and operation is not IS_BLANK or IS_NOT_BLANK.
     * @throws Exception If the value array contains an empty string.
     * @throws Exception If the operation does not require array values and value is an array.
     * @throws Exception If the operation requires two values and value is not an array.
     * @throws Exception If the operation requires two values and the value array count is not equal to 2.
     * @throws Exception If the operation requires at least one value and the value array is empty.
     */
    private function validateValue(string $operation, string|array|null $value): void
    {
        if(($operation !== Operations::IS_BLANK && $operation !== Operations::IS_NOT_BLANK) && !isset($value)){
            throw new Exception("Value cannot be empty");
        }

        if(is_array($value)){
            foreach ($value as $val){
                if(empty($val)){
                    throw new Exception("Values cannot contain an empty string");
                }
            }
        }

        if(($operation !== Operations::IS_IN_BETWEEN &&
            $operation !== Operations::IS_NOT_IN_BETWEEN &&
            $operation !== Operations::IS_IN &&
            $operation !== Operations::IS_NOT_IN) &&
            is_array($value)){
            throw new Exception("Operation $operation does not require array values");
        }

        if(($operation === Operations::IS_IN_BETWEEN || $operation === Operations::IS_NOT_IN_BETWEEN) && !is_array($value)){
            throw new Exception("Operation $operation requires two values");
        }

        if(($operation === Operations::IS_IN_BETWEEN || $operation === Operations::IS_NOT_IN_BETWEEN) && count($value) !== 2){
            throw new Exception("Operation $operation requires two values");
        }

        if(($operation === Operations::IS_IN || $operation === Operations::IS_NOT_IN) && !is_array($value)){
            throw new Exception("Operation $operation requires an array values");
        }

        if(($operation === Operations::IS_IN || $operation === Operations::IS_NOT_IN) && empty($value)){
            throw new Exception("Operation $operation requires at least one value");
        }

    }
}
