<?php

namespace Tactics\TableBundle\Tests;

use Tactics\TableBundle\Table;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\Column;
use Tactics\TableBundle\CsvDataTransformer;

class CsvDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Tactics\TableBundle\CsvDataTransformer::transform
     */
    public function testTransform()
    {
        $table = new Table('Table');

        $table
            ->add(new Column('Name', new ColumnHeader('Name')))
            ->add(new Column('Age', new ColumnHeader('Age')))
            ->setRows(array(
                array('Name' => 'Aaron','Age' => '23'),
                array('Name' => 'Joris', 'Age' => '35'),
            ))
        ;
        
        $csv = "Name;Age\r\nAaron;23\r\nJoris;35";

        $this->assertEquals($csv, $table->transformData(new CsvDataTransformer()));
    }

    /**
     * Row values that are objects should be transformed to a string correctly.
     * \DateTime should get formatted "d/m/Y", all other objects should be cast 
     * to string.
     *
     * @covers Tactics\TableBundle\CsvDataTransformer::transform
     */
    public function testDateTimeAndObjectFormatting()
    {
        $table  = new Table('Table');
        $person = new StringablePerson('Aaron');

        $table
            ->add(new Column('Name', new ColumnHeader('Name')))
            ->add(new Column('Birthdate', new ColumnHeader('Birthdate')))
            ->setRows(array(
                array('Name' => $person, 'Birthdate' => $person->getBirthdate())
            ))
        ;

        $date = new \DateTime();

        $csv = "Name;Birthdate\r\nAaron;{$date->format('d/m/Y')}";

        $this->assertEquals($csv, $table->transformData(new CsvDataTransformer()));
    }

    /**
     * Test wheher cell value that is an object but cannot be cast to string is 
     * transformed to an empty string.
     *
     * @covers Tactics\TableBundle\CsvDataTransformer::transform
     */
    public function testNonStringableObjectFormatting()
    {
        $table  = new Table('Table');
        $person = new Person('Aaron');

        $table
            ->add(new Column('Name', new ColumnHeader('Name')))
            ->add(new Column('Birthdate', new ColumnHeader('Birthdate')))
            ->setRows(array(
                array('Name' => $person, 'Birthdate' => $person->getBirthdate())
            ))
        ;

        $date = new \DateTime();

        $csv = "Name;Birthdate\r\n;{$date->format('d/m/Y')}";

        $this->assertEquals($csv, $table->transformData(new CsvDataTransformer()));
    }
}

/**
 * Dummy classes for testing the formatting of a \DateTime object and 
 * formatting of an object.
 */
class Person
{
    protected $name;

    protected $birthdate;

    public function __construct($name)
    {
        $this->name = $name;
        $this->birthdate = new \DateTime();
    }
    public function getBirthdate()
    {
        return $this->birthdate;
    }
}

class StringablePerson extends Person
{
    public function __toString()
    {
        return $this->name;
    }
}
