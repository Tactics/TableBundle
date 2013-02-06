<?php

namespace Tactics\TableBundle\Tests;

use Tactics\TableBundle\Table;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\Column;
use Tactics\TableBundle\CsvDataTransformer;

class CsvDataTransformerTest extends \PHPUnit_Framework_TestCase
{
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
}
