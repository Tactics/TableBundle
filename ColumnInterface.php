<?php

namespace Tactics\TableBundle;

interface ColumnInterface
{
    
    /**
     * Constructor.
     */
    public function __construct($name, ColumnHeader $header, array $options = array(), $extensions = array());
    
    /**
     * Returns the name of the column
     * 
     * @return String The name of the column.
     */
    function getName();

    /**
     * Returns a the cell info of the given row
     * 
     * @param array $row The current row.
     *
     * @return mixed The cell info of this column in the given row 
     */
    function getCell($row);


    /**
     * Returns the column type
     * 
     * @return string The type of the column.
     */
    function getType();
    
    /**
     * Returns a specific option 
     * 
     * @param string $name
     * 
     * @return mixed
     */
    function getOption($name);
    
    /**
     * Sets an option on the column
     * 
     * @param string $name
     * @param mixed $value
     */
    function setOption($name, $value);
}
