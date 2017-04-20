<?php

namespace Tactics\TableBundle;

interface DataTransformerInterface
{
    public function transform(Table $table);
}
