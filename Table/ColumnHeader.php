<?php

namespace Tactics\TableBundle\Table;

class ColumnHeader implements ColumnHeaderInterface
{
    // @var $value String
    protected $value;

    // @var $column ColumnInterface
    protected $column;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($value, array $attributes = array())
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
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
