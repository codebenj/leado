<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Lead;
use App\LeadEscalation;
use Carbon\Carbon;

class OrganizationManualNotificationExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithEvents, WithStyles
{
    use Exportable;

    protected $organisation;

    private $stages = [
        'Stage 1' => [
            'Confirm Enquirer Contacted',
        ],
        'Stage 2' => [
            'In Progress',
        ],
        'Stage 3' => [
            'Lost',
        ],
        'Stage 4' => [
            'Won',
        ],
    ];

    private $final = ['Won', 'Lost'];

    public function __construct($organisation)
    {
        $this->organisation = $organisation;
    }

    public function query()
    {
        // $now = Carbon::now();
        // $weekStartDate = $now->startOfWeek()->format('Y-m-d H:i');
        // $weekEndDate = $now->endOfWeek()->format('Y-m-d H:i');

        // $organisation_id = $this->organisation->id;

        // return Lead::query()->whereHas('active_escalation', function($q) use($weekStartDate, $weekEndDate, $organisation_id){
        //     $q->where('organisation_id', $organisation_id);
        //     $q->whereBetween('updated_at', [$weekStartDate, $weekEndDate]);
        // })->with(['active_escalation', 'customer.address']);

        $organisation_id = $this->organisation->id;

        return Lead::query()->whereHas('active_escalation', function($q) use($organisation_id){
            $q->where('organisation_id', $organisation_id);
            $q->whereNull('metadata->is_final');
        })->with(['active_escalation', 'customer.address']);
    }

    public function map($lead): array
    {
        $stage = $this->getStage($lead->active_escalation[0]->escalation_level);

        if(in_array($lead->active_escalation[0]->escalation_level, $this->final)){
            $lead_escalation = LeadEscalation::find($lead->active_escalation[0]->id);
            $metadata = $lead_escalation->metadata;
            $metadata['is_final'] = true;
            $lead_escalation->metadata = $metadata;
            $lead_escalation->update();
        }

        return [
            $lead->lead_id ?? $lead->id,
            $lead->customer->first_name . ' ' . $lead->customer->last_name,
            $lead->customer->address->full_address,
            $stage,
            '',
            '',
        ];
    }

    public function headings(): array
    {
        return [
            ['Weekly Lead Update Report'],
            [
                'Lead ID',
                'ENQUIRER NAME',
                'ENQUIRER ADDRESS',
                'STAGE ON RECORD',
                '*NEW/CURRENT STAGE',
                '*COMMENTS',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:F1');
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('K2', 'Lead Stages Explained');
        $sheet->setCellValue('K4', 'Stage 1 - I\'ve yet to make contact with the Enquirer.');
        $sheet->setCellValue('K6', 'Stage 2 - I\'ve made contact with the Enquirer and the Lead is in Progress');
        $sheet->setCellValue('K8', 'Stage 3 - The Lead is Lost (and if so, why)?');
        $sheet->setCellValue('K10', 'Stage 4 - The Lead has been WON & INSTALLED? if so,');
        $sheet->setCellValue('K12', 'How many metres of Gutter Edge');
        $sheet->setCellValue('K13', 'How many metres of Valley');
        $sheet->setCellValue('K14', 'Date of Instalation');

        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '0000ff'],
                ],
            ],
        ];

        $sheet->getStyle('K2:K14')->applyFromArray($styleArray);
        $sheet->getStyle('K2')->getFont()->setBold(true);
        $sheet->getColumnDimension('K')->setWidth(70);
    }

    private function getStage($escalation_level){
        foreach($this->stages as $key => $value){
            if( in_array($escalation_level, $value) ){
                return $key;
            }
        }
        return false;
    }
}
