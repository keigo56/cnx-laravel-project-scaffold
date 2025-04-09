<?php

namespace App\Modules\Datatables\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelWriter;

trait WithExport
{
    private int $maxRows = 100000;

    private string $exportName = '';

    /**
     * @throws Exception
     */
    public function export(): string
    {
        $this->validateFields();

        $fileName = Str::random(20).'.csv';
        $filePath = "/exports/$fileName";
        $temporaryCSVFile = Storage::put($filePath, '');

        if (! $temporaryCSVFile) {
            throw new Exception('Cannot create temporary CSV File');
        }

        $this->writeDataToCSV($filePath);

        return $filePath;
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

        if ($rowCount > $this->maxRows) {
            throw new Exception("The number of rows to be exported is excessive. It is recommended that each export should not exceed $this->maxRows rows.");
        }
    }

    /**
     * @throws Exception
     */
    private function validateExportName(): void
    {
        if ($this->exportName === '') {
            throw new Exception('Please specify the export name');
        }
    }

    private function writeDataToCSV($filePath): void
    {
        $writer = SimpleExcelWriter::create(Storage::path($filePath), 'csv');
        $headers = collect($this->columns->toArray())->pluck('key')->toArray();
        $writer->addHeader($headers);

        $this->queryBuilder->chunk(200, function ($rows) use ($writer) {

            $rows = $rows->toArray();
            $rows = json_decode(json_encode($rows), true);

            $this->rows = $rows;

            if ($this->willMapRow) {
                $this->mapRows();
            }

            $rows = $this->rows;
            $writer->addRows($rows);
        });

        $writer->close();
    }
}
