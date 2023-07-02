<?php

namespace App\Modules\Datatables\Traits;

use App\Modules\Datatables\Row;
use Exception;

trait WithMapRow
{

    protected bool $willMapRow = false;
    protected mixed $callback;

    /**
     * @throws Exception
     */
    public function mapRow($callback): self
    {
        if(!$callback){
            throw new Exception('Please specify the callback function for mapRow');
        }

        $this->callback = $callback;
        $this->willMapRow = true;

        return $this;
    }

    protected function mapRows(): void
    {
        $formattedRows = [];

        $rows = $this->rows->toArray();

        if(array_key_exists('data', $rows)){
            $rows = $rows['data'];
        }

        foreach ($rows as $row){
            $rowObj = new Row();
            $rowObj->setRowValue($row);
            call_user_func($this->callback, $rowObj);
            $formattedRows[] = $rowObj->getRow();
        }

        $this->rows = $this->rows->toArray();

        if(array_key_exists('data', $this->rows)){
            $this->rows['data'] = $formattedRows;
        }else{
            $this->rows = $formattedRows;
        }

    }

}
