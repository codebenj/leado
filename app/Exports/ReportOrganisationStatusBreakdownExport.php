<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportOrganisationStatusBreakdownExport implements Responsable, FromView
{
    use Exportable;

    public $data;

    private $fileName = 'organisation-status-breakdown.xls';

    private $writerType = Excel::XLS;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('export.excel.organisation-status-breakdown', [
            'data' => $this->data
        ]);
    }
}
