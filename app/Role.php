<?php

namespace App;

use Spatie\Permission\Models\Role as BaseRole;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Role extends BaseRole
{
    use LadaCacheTrait;
}
