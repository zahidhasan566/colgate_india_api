<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class ColgateDataExport implements FromCollection
{
    public function collection()
    {
        // Get today's date
        $dateToObj = Carbon::now(); // Current date and time
        $dateFromObj = $dateToObj->copy()->subHours(24); // Subtract 24 hours

        // Fetch data from stored procedure
        return collect(DB::select("exec usp_loadColgateIndiaApiData '$dateFromObj','$dateToObj'"));
    }
}
