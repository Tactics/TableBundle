<?php

namespace Tactics\TableBundle\Table;

class Column implements ColumnInterface
{
    // @var $header ColumnHeaderInterface
    protected $header;

    // @var $row ColumnCellInterface
    protected $cell;

    // @var $name String The name of the column.
    protected $name;

    /**
     * Constructor.
     */
    public function __construct($name, ColumnHeader $header, ColumnCell $cell, array $attributes = array())
    {
        $this->name   = $name;
        $this->header = $header;
        $this->cell   = $cell;
        
        $this->header->setColumn($this);
        $this->cell->setColumn($this);
    }

    /**
     * @return ColumnHeader 
     */
    public function getHeader()
    {
        return $this->header;
    }
    
    public function renderHeader()
    {
      return $this->getHeader()->render($name);
    }

    /**
     * @return ColumnCellInterface
     */
    public function getCell()
    {
        return $this->cell;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
} 
