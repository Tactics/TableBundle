<?php

namespace Tactics\TableBundle\Table;

class ColumnCell implements ColumnCellInterface
{
    
    // @var $column ColumnInterface
    protected $column;
  
    /**
     * {@inheritdoc}
     */
    public function render($value)
    {
        return $value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setColumn(ColumnInterface $column)
    {
        $this->column = $column;
    }
}
