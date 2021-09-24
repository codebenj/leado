<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function query()
    {
        return User::query()->whereIn('id', $this->ids)->with(['user_role']);
    }

    public function map($user): array
    {
        return [
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->user_role->name,
        ];
    }

    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Email Address',
            'Role',
        ];
    }
}
