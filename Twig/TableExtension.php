<?php

namespace Tactics\TableBundle\Twig;

use Tactics\TableBundle\Table\Table;
use Tactics\TableBundle\Table\Column;
use Tactics\TableBundle\Table\ColumnHeader;
use Tactics\TableBundle\Table\ColumnCell;

use Symfony\Component\DependencyInjection\ContainerInterface;

class TableExtension extends \Twig_Extension
{
    /**
    *
    * @var ContainerInterface A ContainerInterface instance.
    */
    protected $container;
    
    /**
     * Constructor
     *
     * @param ContainerInterface $container A ContainerInterface instance.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
          'table_widget' => new \Twig_Function_Method($this, 'renderTable', 
          array('is_safe' => array('html'))),
          'cell' => new \Twig_Function_Method($this, 'renderCell', 
          array('is_safe' => array('html'))),
          'header' => new \Twig_Function_Method($this, 'renderHeader', 
          array('is_safe' => array('html')))
        );
    }

    /**
     * Renders a table.
     *
     * @param Table The Table instance to render.
     */
    public function renderTable(Table $table)
    {
        $request = $this->container->get('request');

        return $this->container->get('templating')->render(
            'TacticsTableBundle::table_widget.html.twig',
            array('table' => $table)
          );
    }

    /**
     * Renders a ColumnCell.
     * 
     * @param ColumnCell The ColumnCell instance to render.
     */
    public function renderCell(Column $column, $value)
    {
        return $this->container->get('templating')->render(
            'TacticsTableBundle::column_cell_'.$column->getType().'.html.twig',
            array('column' => $column, 'value' => $value)
          );
    }

    /**
     * Renders a ColumnHeader.
     * 
     * @param ColumnHeader The ColumnHeader instance to render.
     */
    public function renderHeader(ColumnHeader $header)
    {
        return $this->container->get('templating')->render(
            'TacticsTableBundle::column_header_'.$header->getType().'.html.twig',
            array('header' => $header)
          );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'table';
    }
}
