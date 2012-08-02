<?php

namespace Tactics\TableBundle\Table;

interface ColumnHeaderInterface 
{
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
}
