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
        $dateTo = Carbon::today()->format('Y-m-d');

        // Get date from (3 days before today)
        $dateFrom = Carbon::today()->subDays(3)->format('Y-m-d');

        // Fetch data from stored procedure
        return collect(DB::select("exec usp_loadColgateIndiaApiData '$dateFrom','$dateTo'"));
    }
}
