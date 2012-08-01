<?php

namespace Tactics\TableBundle\Table;

interface ColumnCellInterface
{
    /**
     * Returns the value of the cell.
     *
     * @param String $value The value to return.
     * 
     * @return String
     */
    public function getValue($value);

    /**
     * Sets the column.
     *
     * @param ColumnInterface Instance of a ColumnInterface.
     */
    function setColumn(ColumnInterface $column);

    /**
     * The type is used to determine the twig template location.
     *
     * @return String The column type.
     */
    function getType();
}
