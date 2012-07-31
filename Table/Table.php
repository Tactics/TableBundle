<?php

namespace Tactics\TableBundle\Table;

class Table implements \IteratorAggregate
{
    /**
     * @var array An array of ColumnInterface instances. 
     */
    protected $columns = array();

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

    public function render()
    {      
        $html = '<table class="table"><thead><tr>';

        foreach ($this->columns as $column) {
            $html .= '<th>'.$column->getRenderHeader().'</th>';
        }

        $html .= '</tr></thead>';

        $html .= '<tbody>';

        foreach ($this->rows as $row)
        {
            $html .= '<tr>';

            foreach ($row as $columnName => $value)
            {
                $column = $this->columns[$columnName];
                $html .= '<td>'.$column->getCell()->render($value).'</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
