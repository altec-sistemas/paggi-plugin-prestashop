<?php

namespace Paggi;

use Paggi\Traits\Create;
use Paggi\Traits\FindAll;
use Paggi\Traits\FindById;

class Charge extends Entity
{
    use FindAll, Create, FindById;
}
