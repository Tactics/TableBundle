<?php 

/**
 * Class Csvdatatransformer 
 * @author Aaron Muylaer <aaron@tactics.be>
 */

namespace Tactics\TableBundle;

use Tactics\TableBundle\DataTransformerInterface;

class CsvDataTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $csv = '';

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * Constructor.
     */
    public function __construct($delimiter = ';')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritDoc
     */
    public function transform(Table $table)
    {
        $this->writeHeaders($table);
        $this->writeRows($table);
        $this->removeLastNewLineCharacter();

        return $this->csv;
    }

    /**
     * Writes headers to csv string.
     *
     * @param Tactics\TableBundle\Table $table
     * 
     * @return void
     */
    private function writeHeaders(Table $table)
    {
        foreach ($table as $column) {
            $this->csv .= $column->getHeader()->getValue().$this->delimiter;
        }

        $this->appendNewLineCharacter();
        $this->cleanUpLastRow();
    }

    /**
     * Loops over the rows and writes them to the csv formatted string.
     *
     * @param Tactics\TableBundle\Table $table
     *
     * @return void
     */
    private function writeRows(Table $table)
    {
        foreach ($table->getRows() as $row) {
            $this->csv .= $this->writeRow($table, $row);
        }
    }

    /**
     * Writes a single row to the csv formatted string.
     *
     * @param Tactics\TableBundle\Table $table
     * @param array                     $row
     *
     * @return void
     */
    private function writeRow(Table $table, $row)
    {
        foreach ($table as $column) {
            $cell = $column->getCell($row);
            $this->csv .= $cell['value'].$this->delimiter;
        }

        $this->cleanUpLastRow();
        $this->appendNewLineCharacter();
    }

    /**
     * Appends a newline character to the csv formatted string.
     *
     * @return void
     */
    private function appendNewLineCharacter()
    {
        $this->csv .= "\r\n";
    }

    /**
     * Removes last trailing delimiter from csv string.
     *
     * @return void
     */
    private function cleanUpLastRow()
    {
        $this->csv = $this->str_lreplace($this->delimiter, '', $this->csv);
    }

    /**
     * Removes the last new line character from the csv string.
     *
     * @return void
     */
    private function removeLastNewLineCharacter()
    {
        $this->csv = $this->str_lreplace("\r\n", '', $this->csv);
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
}
