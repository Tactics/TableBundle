<?php

namespace Tactics\TableBundle\Tests;

use Tactics\TableBundle\Table;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\Column;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Tactics\TableBundle\Table::add
     */
    public function testAdd()
    {
        $table = new Table('table'); 
        $table->add(new Column('Foo', new ColumnHeader('Foo')));

        $columns = $table->getColumns();
        $column = end($columns);

        $this->assertEquals('Foo', $column->getName());
    }
}

