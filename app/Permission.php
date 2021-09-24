<?php

namespace App;

use Spatie\Permission\Models\Permission as BasePermission;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Permission extends BasePermission
{
    use LadaCacheTrait;
}
