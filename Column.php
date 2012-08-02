<?php

namespace Tactics\TableBundle;

class Column implements ColumnInterface
{
    /**
     * @var $header ColumnHeaderInterface
     */
    protected $header;

    /**
     * @var $name string The name of the column.
     */
    protected $name;

    /*
     * @var $options array The column options.
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, ColumnHeader $header, array $options = array())
    {
        $this->name    = $name;
        $this->header  = $header;
        $this->options = $options;
        
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
    public function getOptions()
    {
        return $this->options;
    }
} 
