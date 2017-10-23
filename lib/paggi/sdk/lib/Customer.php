<?php

namespace Paggi;

use Paggi\Entity;

use Paggi\Traits\Create;
use Paggi\Traits\Update;
use Paggi\Traits\FindAll;
use Paggi\Traits\FindById;

class Customer extends Entity
{
    use FindAll, Create, FindById, Update;
}

?>
