<?php

namespace App\Http\Controllers\Colgate;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ColgateDataController extends Controller
{
    public function getData(Request $request){
        $validator = Validator::make($request->all(), [
            'dateFrom' => 'required',
            'dateTo' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'error' => $validator->errors()]);
        }
        try{

            $dateFrom = $request->dateFrom;
            $dateTo = $request->dateTo;
            // Parse the input dates using Carbon
            $dateFromObj = Carbon::parse($dateFrom);
            $dateToObj = Carbon::parse($dateTo);

            // Calculate the difference in days
            $daysDifference = $dateFromObj->diffInDays($dateToObj);

            // Validate the range
            if ($daysDifference > 3) {
                return response()->json(['status' => 406, 'message' => "The date range cannot exceed 3 days."]);
            } else {
                $data = DB::select("exec usp_loadColgateIndiaApiData '$dateFrom','$dateTo'");
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
}
