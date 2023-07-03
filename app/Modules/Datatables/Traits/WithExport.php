<?php

namespace App\Modules\Datatables\Traits;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Row;
use Spatie\SimpleExcel\SimpleExcelWriter;
use stdClass;

trait WithExport
{

    private int $maxRows = 5000;
    private string $exportName = '';

    /**
     * @throws Exception
     */
    public function export(): void
    {
        $this->validateFields();

        $writer = SimpleExcelWriter::streamDownload($this->exportName . '.csv');
        $headers = collect($this->columns->toArray())->pluck('key')->toArray();
        $writer->addHeader($headers);
        
        $this->queryBuilder->chunk(200, function($rows) use ($writer){

            $rows = $rows->toArray();
            $rows = json_decode(json_encode($rows), true);

            $this->rows = $rows;

            if($this->willMapRow){
                $this->mapRows();
            }

            $rows = $this->rows;
            $writer->addRows($rows);
        });

        $writer->close();
    }

    public function maxExportRows(int $maxRows): self
    {
        $this->maxRows = $maxRows;
        return $this;
    }

    public function exportName(string $exportName): self
    {
        $this->exportName = $exportName;
        return $this;
    }

    /**
     * @throws Exception
     */
    private function validateFields(): void
    {
        $this->validateRowCount();
        $this->validateExportName();
    }

    /**
     * @throws Exception
     */
    private function validateRowCount(): void
    {
        $rowCount = $this->queryBuilder->count();

        if($rowCount > $this->maxRows){
            throw new Exception("The number of rows to be exported is excessive. It is recommended that each export should not exceed $this->maxRows rows.");
        }
    }

    /**
     * @throws Exception
     */
    private function validateExportName(): void
    {
        if($this->exportName === ''){
            throw new Exception("Please specify the export name");
        }
    }
}
