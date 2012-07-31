<?php

namespace Tactics\TableBundle\Table;

interface ColumnHeaderInterface 
{
    /**
     * Renders the header.
     *
     * @return String
     */
    function render();
    
    /**
     * Sets the column.
     *
     * @param ColumnInterface A ColumnInterface instance
     */    
    function setColumn(ColumnInterface $column);
}
