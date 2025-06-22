<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ColgateDataExport implements FromCollection,WithHeadings
{
    public function collection()
    {
//        $dateToObj = Carbon::now();
//        $dateFromObj = $dateToObj->copy()->subHours(24);

        $dateToObj = '2025-04-06';
        $dateFromObj = '2025-04-07';

        $data = collect(DB::select("exec usp_loadColgateIndiaApiData '$dateFromObj','$dateToObj'"));

        return $data;
    }

    public function headings(): array
    {
//        $dateToObj = Carbon::now();
//        $dateFromObj = $dateToObj->copy()->subHours(24);

        $dateToObj = '2025-04-06';
        $dateFromObj = '2025-04-07';

        $data = collect(DB::select("exec usp_loadColgateIndiaApiData '$dateFromObj','$dateToObj'"));

        return $data->isNotEmpty() ? array_keys((array) $data->first()) : [];
    }
}
