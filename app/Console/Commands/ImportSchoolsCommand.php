<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SchoolImportService;
use Illuminate\Support\Facades\Log;

class ImportSchoolsCommand extends Command
{
    protected $signature = 'import:schools';
    protected $description = 'Import schools from a CSV file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting the import process...');

        try {
            $importService = new SchoolImportService();

            $importService->importSchools();

            $this->info('Import process completed successfully.');
        } catch (\Exception $e) {
            Log::error('Error during school import', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->error('An error occurred: ' . $e->getMessage());
            $this->error('Error on file: ' . $e->getFile() . ' Line: ' . $e->getLine());

            if ($this->option('verbose')) {
                $this->info('Additional error details: ' . $e->getTraceAsString());
            }
        }
    }
}
