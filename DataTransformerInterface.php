<?php

namespace Tactics\TableBundle;

use Tactics\TableBundle\Table;

interface DataTransformerInterface
{
    public function transform(Table $table);
}
