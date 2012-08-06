<?php

namespace Tactics\TableBundle;

interface ColumnHeaderInterface 
{
    /**
     * Constructor.
     *
     * @param $value String Value inside of the header.
     */
    function __construct($value, array $attributes = array(), array $options = array());
    
    /**
     * Sets the column.
     *
     * @param ColumnInterface A ColumnInterface instance
     */    
    function setColumn(ColumnInterface $column);

    /**
     * The type is used to determine the twig template location.
     *
     * @return String The column type.
     */
    function getType();

    /**
     * @return String The value.
     */
    function getValue();

    /**
     * @return array The attributes.
     */
    function getAttributes();
}
