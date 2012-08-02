<?php

namespace Tactics\TableBundle\Table;

interface ColumnInterface
{
    
    /**
     * Constructor.
     */
    public function __construct($name, ColumnHeader $header, array $attributes = array());
    
    /**
     * @return String The name of the column.
     */
    function getName();

    /**
     * @param mixed $value The value.
     *
     * @return mixed $value The formatted value.
     */
    function getValue($value);


    /**
     * @return string The type of the column.
     */
    function getType();
}
