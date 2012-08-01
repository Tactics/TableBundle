<?php

namespace Tactics\TableBundle\Table;

class ColumnCell implements ColumnCellInterface
{
    
    // @var $column ColumnInterface
    protected $column;
  
    /**
     * {@inheritdoc}
     */
    public function getValue($value)
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

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'default';
    }
}
