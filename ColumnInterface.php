<?php

namespace Tactics\TableBundle;

interface ColumnInterface
{
    
    /**
     * Constructor.
     */
    public function __construct($name, ColumnHeader $header, array $attributes = array(), $extensions = array());
    
    /**
     * @return String The name of the column.
     */
    function getName();

    /**
     * @param array $row The current row.
     *
     * @return mixed The cell info of this column in the given row 
     */
    function getCell($row);


    /**
     * @return string The type of the column.
     */
    function getType();
    
    function getOption($name);
    
    function setOption($name, $value);
}
