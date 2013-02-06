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
     * @covers \Tactics\TableBundle\Table::__construct
     * @covers \Tactics\TableBundle\Table::setDefaultOptions
     * @covers \Tactics\TableBundle\Table::getOptions
     * @covers \Tactics\TableBundle\Table::getOption
     */
    public function testConstructor()
    {
        $options = array('attributes' => array(
            'foo'   => 'bar'
        ));

        $table = new Table('Foo', $options);

        $this->assertEquals($options, $table->getOptions());
        $this->assertNull($table->getOption('bar'));
        $this->assertEquals(array('foo' => 'bar'), $table->getOption('attributes'));
    }

    /**
     * @depends testConstructor
     * @expectedException Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testOptionException()
    {
        $table = new Table('Foo', array('Foo' => 'Bar'));
    }

    /**
     * @covers \Tactics\TableBundle\Table::getIterator
     */
    public function testGetIterator()
    {
        $this->assertInstanceOf('ArrayIterator', $this->table->getIterator());
    }

    /**
     * @depends testGetIterator
     * @covers Tactics\TableBundle\Table::add
     */
    public function testAdd()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));
        $this->table->add($column);

        $iterator = $this->table->getIterator();
        $columns = $iterator->getArrayCopy();

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
     * @depends testOffsetGet
     * @covers Tactics\TableBundle\Table::offsetSet
     */
    public function testOffsetSet()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));
        $column2 = new Column('Bar', new ColumnHeader('Bar'));

        $this->table->offsetSet($column->getName(), $column);
        $this->table->offsetSet(null, $column2);

        $this->assertEquals($column, $this->table->offsetGet($column->getName()));

        $iterator = $this->table->getIterator();
        $columns  = $iterator->getArrayCopy();

        $this->assertEquals($column2, end($columns));
    }

    /**
     * @depends testOffsetSet
     * @depends testOffsetExists
     * @covers Tactics\TableBundle\Table::offsetUnset
     */
    public function testOffsetUnset()
    {
        $column = new Column('Foo', new ColumnHeader('Foo'));

        $this->table->offsetSet($column->getName(), $column);

        $this->table->offsetUnset($column->getName());

        $this->assertFalse($this->table->offsetExists('Foo'));
    }

    /**
     * @depends testAdd
     * @depends testOffsetUnset
     * @covers \Tactics\TableBundle\Table::count
     */
    public function testCount()
    {
        $this->table->add(new Column('Foo', new ColumnHeader('Foo')))
            ->add(new Column('Bar', new ColumnHeader('Bar')))
            ->add(new Column('Baz', new ColumnHeader('Baz')));

        $this->assertEquals(3, $this->table->count());

        $this->table->offsetUnset('Foo');

        $this->assertEquals(2, $this->table->count());

        $this->table->offsetUnset('Baz');

        $this->assertEquals(1, $this->table->count());

        $this->table->offsetUnset('Bar');

        $this->assertEquals(0, $this->table->count());
    }

    /**
     * @covers Tactics\TableBundle\Table::exportToCsv
     */
    public function testExportToCsv()
    {
        $this->table
            ->add(new Column('Name', new ColumnHeader('Name')))
            ->add(new Column('Age', new ColumnHeader('Age')))
            ->setRows(array(
                array('Name' => 'Aaron','Age' => '23'),
                array('Name' => 'Joris', 'Age' => '35'),
            ))
        ;

        $csv = "Name;Age;\r\nAaron;23\r\nJoris;35";

        $this->assertEquals($csv, $this->table->exportToCsv());
    }
}
