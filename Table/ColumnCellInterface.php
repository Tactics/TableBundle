<?php

namespace Tactics\TableBundle\Table;

interface ColumnCellInterface
{
    /**
     * Renders the value of the cell.
     *
     * @param String $value The value to render.
     * 
     * @return String
     */
    function render($value);

    /**
     * Sets the column.
     *
     * @param ColumnInterface Instance of a ColumnInterface.
     */
    function setColumn(ColumnInterface $column);
}
