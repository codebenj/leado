<?php

namespace App;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Model extends BaseModel {
    use LadaCacheTrait;
}
