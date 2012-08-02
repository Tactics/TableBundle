<?php

namespace Tactics\TableBundle\Table;

class Table implements \IteratorAggregate
{
    /**
     * @var array An array of ColumnInterface instances. 
     */
    protected $columns = array();

    /**
     * @var array An array of rows.
     */
    protected $rows = array();

    // @var array An array of attributes.
    protected $attributes = array();

    /**
     * Returns an iterator for columns
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->columns);
    }

    /**
     * Adds a column to the table.
     *
     * @param ColumnInterface $column The ColumnInterface to add.
     * 
     * @return Table The current table.
     */
    public function add(ColumnInterface $column)
    {
        $this->columns[$column->getName()] = $column; 

        return $this;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function getRows()
    {
      return $this->rows;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
