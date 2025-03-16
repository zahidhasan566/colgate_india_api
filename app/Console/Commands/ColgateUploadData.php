<?php

namespace App\Console\Commands;
use App\Exports\ColgateDataExport;
use App\Services\SFTPService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ColgateUploadData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'colgateData:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data to Excel, upload to SFTP';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SFTPService $sftpService)
    {
        parent::__construct();
        $this->sftpService = $sftpService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Generate Excel file
            $fileName = 'colgate_data_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = storage_path("app/exports/{$fileName}");

            // Store the file in the storage directory
            Excel::store(new ColgateDataExport(), "exports/{$fileName}");

            $this->info("Excel file created: $fileName");

            // Check if the file exists
            if (!file_exists($filePath)) {
                throw new \Exception("File not found at path: $filePath");
            }

            // Upload file to SFTP
            $this->info("Uploading file to SFTP...");
            $this->sftpService->uploadFile($filePath, $fileName);

            $this->info("Process completed successfully!");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
