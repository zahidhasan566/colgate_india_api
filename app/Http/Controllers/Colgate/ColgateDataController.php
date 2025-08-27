<?php

namespace App\Http\Controllers\Colgate;

use App\Exports\ColgateDataExport;
use App\Http\Controllers\Controller;
use App\Services\SFTPService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;

class ColgateDataController extends Controller
{
    public $sftpService;
    public function __construct(SFTPService $sftpService)
    {
        $this->sftpService = $sftpService;
    }
    public function getData(Request $request){
        $validator = Validator::make($request->all(), [
            'dateFrom' => 'required',
            'dateTo' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'error' => $validator->errors()]);
        }
        try{

//            $dateFrom = $request->dateFrom;
//            $dateTo = $request->dateTo;
            // Parse the input dates using Carbon
            $dateToObj = Carbon::now(); // Current date and time
            $dateFromObj = $dateToObj->copy()->subHours(24); // Subtract 24 hours

            // Calculate the difference in days
            $daysDifference = $dateFromObj->diffInDays($dateToObj);

             // Validate the range
            if ($daysDifference >= 1) {
                return response()->json(['status' => 406, 'message' => "The date range cannot exceed 1 day."]);
            } else {
                $data = DB::select("exec usp_loadColgateIndiaApiData '$dateFromObj', '$dateToObj'");
                return response()->json($data, 200);
            }

        }
        catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong!' . $exception->getMessage() . '-' . $exception->getLine()
            ], 500);
        }


    }


    public function getColgateData(){
        try {
            // Generate Excel file
            $fileName = 'colgate_data_' . now()->format('Y-m-d') . '.csv';
            $filePath = storage_path("app/exports/{$fileName}");

            // Store the file in the storage directory
//            Excel::store(new ColgateDataExport(), "exports/{$fileName}");
//            Excel::store(new ColgateDataExport(), "exports/{$fileName}", null, \Maatwebsite\Excel\Excel::CSV,    [
//                'delimiter' => ',',     // comma-separated
//                'enclosure' => '`',       // no double quotes
//                'use_bom'   => true,    // optional: helps with UTF-8 support in Excel
//            ]);

            //        $dateToObj = Carbon::now();
//        $dateFromObj = $dateToObj->copy()->subHours(24);
//            $dateFrom = '2025-04-01 00:00:00';
//            $dateTo = '2025-04-30 23:59:59';

            $dateTo =  Carbon::now();
            $dateFrom =  $dateTo->copy()->subHours(24);

            $data = collect(DB::select("exec usp_loadColgateIndiaApiData '$dateFrom', '$dateTo'"));

// Make sure directory exists
            Storage::makeDirectory('exports');


// Determine headers
            $headers = $data->isNotEmpty()
                ? array_keys((array) $data->first())
                : [
                    'ACI_Invoice_Number', 'Artical', 'BatchNo', 'Quantity', 'SalesUnit',
                    'ALTERNATE_QUANTITY', 'ALTERNATE_SALES_UNIT',
                    'Storage_Location', 'Distributor_Code', 'InvoiceDate'
                ];

            $handle = fopen($filePath, 'w');

// Write header row
            fputcsv($handle, $headers, ',', "\0");

// Write data rows (if any)
            foreach ($data as $row) {
                fputcsv($handle, array_values((array) $row), ',', "\0");
            }

            fclose($handle);

// Always upload, even if the file is header-only
            $this->sftpService->uploadFile($filePath, $fileName);
            return response()->json([
                'message' => 'Success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        }
    }
}
