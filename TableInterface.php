<?php

namespace Tactics\TableBundle;

use Tactics\TableBundle\ColumnInterface;

/**
 * Description of TableInterface
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
interface TableInterface  extends \ArrayAccess, \Traversable, \Countable
{
    /**
     * Adds a column to the table.
     *
     * @param ColumnInterface $column The ColumnInterface to add.
     * 
     * @return Table The current table.
     */
    public function add(ColumnInterface $column);
    
    /**
     * Sets the data of the table rows
     * 
     * @param type $rows
     * 
     * @return Table The current table
     */
    public function setRows($rows);
    
    /**
     * Returns the table row data
     * 
     * @return \Traversable The row data
     */
    public function getRows();
    
}

