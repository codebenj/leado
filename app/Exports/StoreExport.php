<?php

namespace App\Exports;

use App\Store;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StoreExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function map($store): array
    {
        return [
            $store->code ?? '',
            $store->name ?? '',
            $store->phone_number ?? '',
            $store->last_year_sales ?? '',
            $store->year_to_date_sales ?? '',
            $store->pricing_book,
            $store->priority ?? '',
            $store->stock_kits ?? '',
        ];
    }

    public function query()
    {
        return Store::whereIn('id', $this->ids);
    }

    public function headings(): array
    {
        return [
            'Org. ID',
            'Store Name',
            'Contact Number',
            'Last Year Sales',
            'Year To Date Sales',
            'Pricing Book',
            'Priority',
            'Stock Kits',
        ];
    }
}
