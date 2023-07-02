<?php

namespace App\Modules\Datatables\Enums;

class Operations {
    const DOES_NOT_CONTAIN = 'DOES_NOT_CONTAIN';
    const CONTAINS = 'CONTAINS';
    const EQUALS = 'EQUALS';
    const DOES_NOT_EQUAL = 'DOES_NOT_EQUAL';
    const GREATER_THAN = 'GREATER_THAN';
    const LESS_THAN = 'LESS_THAN';
    const GREATER_THAN_EQUAL = 'GREATER_THAN_EQUAL';
    const LESS_THAN_EQUAL = 'LESS_THAN_EQUAL';
    const STARTS_WITH = 'STARTS_WITH';
    const ENDS_WITH = 'ENDS_WITH';
    const IS_NOT_IN = 'IS_NOT_IN';
    const IS_IN = 'IS_IN';
    const IS_IN_BETWEEN = 'IS_IN_BETWEEN';
    const IS_NOT_IN_BETWEEN = 'IS_NOT_IN_BETWEEN';
    const IS_BLANK = 'IS_BLANK';
    const IS_NOT_BLANK = 'IS_NOT_BLANK';


    public static function get(): array {
        return [
            Operations::DOES_NOT_CONTAIN,
            Operations::CONTAINS,
            Operations::EQUALS,
            Operations::DOES_NOT_EQUAL,
            Operations::GREATER_THAN,
            Operations::LESS_THAN,
            Operations::GREATER_THAN_EQUAL,
            Operations::LESS_THAN_EQUAL,
            Operations::STARTS_WITH,
            Operations::ENDS_WITH,
            Operations::IS_NOT_IN,
            Operations::IS_IN,
            Operations::IS_IN_BETWEEN,
            Operations::IS_NOT_IN_BETWEEN,
            Operations::IS_BLANK,
            Operations::IS_NOT_BLANK,
        ];
    }
}

