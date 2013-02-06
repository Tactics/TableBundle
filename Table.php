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
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
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
        return isset($this->columns[$name]);
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

    /**
     * Export the table to CSV.
     *
     * Should I use fputcsv? I am kind of reinventing the wheel here.
     * The problem I have with fputcsv is that it writes to a physical file...
     *
     * @return string $csv The table formatted as CSV.
     */
    public function exportToCsv()
    {
        $csv = $this->createCsvHeaders();

        foreach ($this->getRows() as $row) {
            $csv .= $this->createCsvRow($row);
        }

        return $this->removeLastNewLineCharacter($csv);
    }

    /**
     * Creates a new string that will act as the CSV and writes the table 
     * headers to it.
     *
     * @return string
     */
    private function createCsvHeaders()
    {
        $csv = '';

        foreach ($this as $column) {
            $csv .= sprintf('%s;', $column->getHeader()->getValue());
        }

        $csv .= $this->createNewLineCharacter();

        return $this->cleanUpLastRow($csv);
    }

    /**
     * Remove trailing delmiter from last row.
     *
     * @param string $csv
     * @return string
     */
    private function cleanUpLastRow($csv)
    {
        return $this->str_lreplace(';', '', $csv);
    }

    /**
     * Appends a newline character to the csv.
     *
     * @param string $csv
     *
     * @return string
     */
    private function createNewLineCharacter()
    {
        return "\r\n";
    }

    /**
     * Replaces last occurence of a string in a string.
     *
     * @param string $search  The string that needs to be replaced.
     * @param string $replace The replacement.
     * @param string $subject The string to search in.
     *
     * @return string
     */
    private function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * Creates a CSV row for a table row.
     *
     * @return string $data
     */
    private function createCsvRow($row)
    {
        $data = '';

        foreach ($this as $column) {
            $cell = $column->getCell($row);
            $data .= sprintf('%s;', $cell['value']);
        }

        $data = $this->cleanupLastRow($data);
        $data .= $this->createNewLineCharacter();

        return $data;
    }

    /**
     * Removes the last new line character for a csv formatted string.
     *
     * @param string $csv
     *
     * @return string $csv
     */
    private function removeLastNewLineCharacter($csv)
    {
        return $this->str_lreplace("\r\n", '', $csv);
    }
}
