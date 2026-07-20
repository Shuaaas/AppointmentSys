<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlantillaItem;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportDataExcelCommand extends Command
{
    protected $signature = 'import:data-excel';
    protected $description = 'Import DATA.xlsx into plantilla_items table';

    public function handle(): void
    {
        $filePath = base_path('DATA.xlsx');

        if (! file_exists($filePath)) {
            $this->error('DATA.xlsx not found in project root.');
            return;
        }

        $this->info('Loading DATA.xlsx...');

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $this->info("Found {$highestRow} rows (including header). Importing...");

        DB::transaction(function () use ($sheet, $highestRow, $highestColumn) {
            PlantillaItem::query()->delete();

            $batch = [];
            $batchSize = 1000;
            $skipped = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $dataValue = $this->cellValue($sheet, 'E', $row);

                if (empty($dataValue)) {
                    $skipped++;
                    continue;
                }

                $batch[] = [
                    'level'                     => $this->cellValue($sheet, 'A', $row),
                    'school_id'                 => $this->cellValue($sheet, 'B', $row),
                    'school_name'               => $this->cellValue($sheet, 'C', $row),
                    'city_municipality'         => $this->cellValue($sheet, 'D', $row),
                    'data'                      => $dataValue,
                    'position'                  => $this->cellValue($sheet, 'F', $row),
                    'sex'                       => $this->cellValue($sheet, 'G', $row),
                    'eligibility'               => $this->cellValue($sheet, 'H', $row),
                    'first_time_used_of_eligibility' => $this->cellValue($sheet, 'I', $row),
                    'position_level'            => $this->cellValue($sheet, 'J', $row),
                    'nature_of_appointment'     => $this->cellValue($sheet, 'K', $row),
                    'status_of_appointment'     => $this->cellValue($sheet, 'L', $row),
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];

                if (count($batch) >= $batchSize) {
                    PlantillaItem::insert($batch);
                    $batch = [];
                    $this->output->write('.');
                }
            }

            if (! empty($batch)) {
                PlantillaItem::insert($batch);
            }

            if ($skipped > 0) {
                $this->warn("Skipped {$skipped} rows with empty data.");
            }
        });

        $this->info('');
        $this->info('Import complete.');
    }

    private function cellValue($sheet, string $column, int $row): ?string
    {
        $value = $sheet->getCell($column . $row)->getValue();

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
