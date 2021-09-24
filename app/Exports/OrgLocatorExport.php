<?php

namespace App\Exports;

use App\OrgLocator;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrgLocatorExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function query()
    {
        return OrgLocator::query()->whereIn('id', $this->ids);
    }

    public function map($orglocator): array
    {
        return [
            $orglocator->org_id,
            $orglocator->name,
            $orglocator->street_address,
            $orglocator->suburb,
            $orglocator->state,
            $orglocator->postcode,
            $orglocator->phone,
            $orglocator->last_year_sales,
            $orglocator->year_to_date_sales,
            $orglocator->pricing_book,
            $orglocator->priority,
            //$orglocator->keeps_stock,
        ];
    }

    public function headings(): array
    {
        return [
            'Org ID',
            'Org',
            'Address',
            'Suburb',
            'State',
            'Postcode',
            'Phone',
            'LYSales',
            'YTDSales',
            'Pbook',
            'Priority',
            //'Stock Kits',
        ];
    }
}
