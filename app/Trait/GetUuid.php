<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;

trait GetUuid
{
    public function uuid()
    {
        $uuid4 = Uuid::uuid4();
        return $uuid4->toString();
    }
}