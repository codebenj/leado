<?php

namespace App\Exports;

use App\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function map($customer): array
    {
        $full_name = $customer->first_name.' '.$customer->last_name;

        return [
            $full_name ?? '',
            $customer->contact_number ?? '',
            $customer->email ?? '',
            $customer->address->suburb ?? '',
            $customer->address->state ?? '',
            $customer->address->postcode ?? '',
            $customer->created_at,
            $customer->lead->customer_type ?? '',
        ];
    }

    public function query()
    {
        return Customer::whereIn('id', $this->ids)->with(['address', 'lead']);
    }

    public function headings(): array
    {
        return [
            'Enquirer Name',
            'Contact Number',
            'Email',
            'Suburb',
            'State',
            'Pcode',
            'Created Date',
            'Lead Type',
        ];
    }
}
