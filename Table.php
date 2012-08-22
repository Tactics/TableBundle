<?php

namespace Tactics\TableBundle;

use Tactics\TableBundle\TableInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Table implements \IteratorAggregate, TableInterface
{
    /**
     * @var array An array of ColumnInterface instances. 
     */
    protected $columns = array();

    /**
     * @var array An array of rows.
     */
    protected $rows = array();
    
    /**
     * @var string The name of the table.
     */
    protected $name;
    
    /**
     * @var array The options of the table.
     */
    protected $options = array();
    
    /**
     * Creates a new table with the given name and options.
     * 
     * @param string $name The name of the table.
     * @param array $options Options to configure the table.
     */
    public function __construct($name, array $options = array())
    {
        $this->name = $name;
        
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        
        $this->options = $resolver->resolve($options);
    }

    /**
     * @inheritDoc
     */
    public function add(ColumnInterface $column)
    {
        $this->columns[$column->getName()] = $column; 

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRows()
    {
      return $this->rows;
    }

    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setOptional(array('attributes'));
    }
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
    
    /**
     * Returns a column by name (implements \ArrayAccess).
     *
     * @param string $name The column name
     *
     * @return Column The column
     */
    public function offsetGet($offset)
    {
        return isset($this->columns[$offset]) ? $this->columns[$offset] : null;
    }
    
    
    /**
     * Returns whether the given child exists (implements \ArrayAccess).
     *
     * @param string $name The child name
     *
     * @return Boolean Whether the child view exists
     */
    public function offsetExists($name)
    {
        return isset($this->children[$name]);
    }

    /**
     * Implements \ArrayAccess.
     *
     * @param string $name The column name
     * @param Column $column The Column
     */
    public function offsetSet($name, $column)
    {
        if (is_null($name))
        {
            $this->columns[] = $column;
        }
        else
        {
            $this->columns[$name] = $column;
        }
    }

    /**
     * Removes a column (implements \ArrayAccess).
     *
     * @param string $name The column name
     */
    public function offsetUnset($name)
    {
        unset($this->columns[$name]);
    }
    
    /**
     * Returns an iterator to iterate over columns (implements \IteratorAggregate)
     *
     * @return \ArrayIterator The iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->columns);
    }

    /**
     * Implements \Countable.
     *
     * @return integer The number of columns
     */
    public function count()
    {
        return count($this->columns);
    }
}
