<?php

namespace Tactics\TableBundle\Tests;

use Tactics\TableBundle\Table;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\Column;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableInterface A TableInterface instance.
     */
    private $table;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->table = new Table('table'); 
    }

    /**
     * @covers Tactics\TableBundle\Table::add
     * @covers Tactics\TableBundle\Table::getColumns
     */
    public function testAddAndGetColumns()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));
        $this->table->add($column);

        $columns = $this->table->getColumns();

        $this->assertEquals($column, end($columns));
    }

    /**
     * @covers Tactics\TableBundle\Table::setRows
     * @covers Tactics\TableBundle\Table::getRows
     */
    public function testSetAndGetRows()
    {
        $rows = array(
            array('Foo', 'Bar', 'Baz')
        );

        $this->table->setRows($rows);

        $this->assertEquals($rows, $this->table->getRows());
    }

    /**
     * @covers Tactics\TableBundle\Table::offsetGet
     */
    public function testOffsetGet()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));

        $this->table->add($column);

        $this->assertEquals($column, $this->table->offsetGet('Foo'));
        $this->assertNull($this->table->offsetGet('Bar'));
    }

    /**
     * @covers Tactics\TableBundle\Table::offsetExists
     */
    public function testOffsetExists()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));

        $this->table->add($column);

        $this->assertEquals(false, $this->table->offsetExists('foo'));
        $this->assertEquals(true, $this->table->offsetExists('Foo'));
    }

    /**
     * @depends testOffsetGet
     * @covers Tactics\TableBundle\Table::offsetExists
     */
    public function testOffsetSet()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));

        $this->table->offsetSet($column->getName(), $column);

        $this->assertEquals($column, $this->table->offsetGet($column->getName()));
    }

    /**
     * @depends testOffsetSet
     * @depends testOffsetExists
     * @covers Tactics\TableBundle\Table::offsetExists
     */
    public function testOffsetUnset()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));

        $this->table->offsetSet($column->getName(), $column);

        $this->table->offsetUnset($column->getName());

        $this->assertFalse($this->table->offsetExists('Foo'));
    }
}
