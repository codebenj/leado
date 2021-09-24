<?php

namespace App\Exports;

use App\Organisation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrganizationsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function query()
    {
        return Organisation::query()->whereIn('id', $this->ids)->with(['address.country', 'user']);
    }

    public function map($organisation): array
    {
        return [
            $organisation->org_code,
            $organisation->name,
            $organisation->contact_number,
            $organisation->user->email,
            $organisation->address->suburb,
            $organisation->address->state,
            $organisation->address->postcode,
            ($organisation->pending_leads == 0) ? '0' : $organisation->pending_leads,
            ($organisation->admin_notified == 0) ? '0' : $organisation->admin_notified,
            //($organisation->is_suspended == 0) ? 'Unsuspended' : 'Suspended',
            $organisation->account_status_type,
            ($organisation->org_status == 0) ? 'Active' : 'Inactive',
        ];
    }

    public function headings(): array
    {
        return [
            'Org Code',
            'Company Name',
            'Contact No.',
            'Primary Email',
            'Suburb',
            'State',
            'Pcode',
            'Leads Pending',
            'Admin Notified',
            'Account Status',
            'Org Status',
        ];
    }
}
