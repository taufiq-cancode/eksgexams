<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SchoolImportService
{
    public function importSchools()
    {
        $totalRecords = $successfulImports = $failedImports = 0;

        try {
            DB::beginTransaction();

            $filePath = storage_path("app/excel/ss2_private.xlsx");
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $batchData = [];

            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) continue;

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                $cells = [];

                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                $totalRecords++;
                $lgCode = $cells[0];
                $owner = $cells[2];
                $schoolName = $cells[3];
                $schoolCode = $cells[4];

                $lg = DB::table('local_governments')->where('lg_code', $lgCode)->first();
                if (!$lg) {
                    $failedImports++;
                    Log::warning('Local Government not found', ['lg_code' => $lgCode]);
                    continue;
                }

                $existingSchool = DB::table('schools')->where('school_code', $schoolCode)->first();
                if ($existingSchool) {
                    $existingExamType = DB::table('school_exam_type')
                                           ->where('school_id', $existingSchool->id)
                                           ->where('exam_type_id',3)
                                           ->first();
                
                    if (!$existingExamType) {
                        DB::table('school_exam_type')->insert([
                            'school_id' => $existingSchool->id,
                            'exam_type_id' => 3
                        ]);
                    }
                
                    $successfulImports++;
                    continue;
                }

                $batchData[] = [
                    'lg_id' => $lg->id,
                    'owner' => $owner,
                    'school_name' => $schoolName,
                    'school_code' => $schoolCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batchData) >= 500) {
                    $successfulImports += $this->insertSchoolBatch($batchData);
                    $batchData = [];
                }
            }

            if (count($batchData) > 0) {
                $successfulImports += $this->insertSchoolBatch($batchData);
            }

            DB::commit(); 

            Log::info('School import process completed.', [
                'total_records' => $totalRecords,
                'successful_imports' => $successfulImports,
                'failed_imports' => $failedImports,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error during school import', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'total_records' => $totalRecords,
                'successful_imports' => $successfulImports,
                'failed_imports' => $failedImports,
            ]);
        }   
    }

    private function insertSchoolBatch($batchData)
    {
        DB::table('schools')->insert($batchData);

        $lastInsertedId = DB::getPdo()->lastInsertId();
        $countInserted = count($batchData);

        for ($i = 0; $i < $countInserted; $i++) {
            $currentSchoolId = $lastInsertedId + $i;
            $currentSchoolCode = $batchData[$i]['school_code']; 

            $this->generatePinForSchool($currentSchoolId, $currentSchoolCode);

            DB::table('school_exam_type')->insert([
                'school_id' => $currentSchoolId,
                'exam_type_id' => 3
            ]);
        }

        return $countInserted;
    }

    private function generatePinForSchool($schoolId, $schoolCode)
    {
        $pin = Str::random(6);
        while (DB::table('pins')->where('pin', $pin)->exists()) {
            $pin = Str::random(6);
        }
        DB::table('pins')->insert([
            'school_id' => $schoolId,
            'school_code' => $schoolCode,
            'pin' => $pin
        ]);
    }
}
