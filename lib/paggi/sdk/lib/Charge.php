<?php

namespace Paggi;

use Paggi\Entity;

use Paggi\Traits\Create;
use Paggi\Traits\FindAll;
use Paggi\Traits\FindById;
use Paggi\Traits\Cancel;
use Paggi\Traits\Capture;

class Charge extends Entity
{
    use FindAll, Create, FindById, Cancel, Capture;
}
