<?php

namespace Tactics\TableBundle;

class Column implements ColumnInterface
{
    // @var $header ColumnHeaderInterface
    protected $header;

    // @var $name string The name of the column.
    protected $name;

    // @var $attributes array The column attributes.
    protected $attributes;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, ColumnHeader $header, array $attributes = array())
    {
        $this->name       = $name;
        $this->header     = $header;
        $this->attributes = $attributes;
        
        $this->header->setColumn($this);
    }

    /**
     * @return ColumnHeader 
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'default';
    }

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
    public function getAttributes()
    {
        return $this->attributes;
    }
} 
