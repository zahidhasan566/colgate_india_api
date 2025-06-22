<?php

namespace App\Http\Controllers\Colgate;

use App\Exports\ColgateDataExport;
use App\Http\Controllers\Controller;
use App\Services\SFTPService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $fileName = 'colgate_data_upp' . now()->format('Y-m-d_H-i-s') . '.csv';
            $filePath = storage_path("app/exports/{$fileName}");

            // Store the file in the storage directory
//            Excel::store(new ColgateDataExport(), "exports/{$fileName}");
            Excel::store(new ColgateDataExport(), "exports/{$fileName}", null, \Maatwebsite\Excel\Excel::CSV,    [
                'delimiter' => ',',     // comma-separated
                'enclosure' => '',      // no double quotes
                'use_bom'   => true,    // optional: helps with UTF-8 support in Excel
            ]);


            // Check if the file exists
            if (!file_exists($filePath)) {
                return response()->json([
                    'message' => 'No file!'
                ],500);
            }

//            $this->info("Uploading file to SFTP...");
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
