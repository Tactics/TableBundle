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
          'table_widget' => new \Twig_Function_Method($this, 'renderTableWidget', 
          array('is_safe' => array('html'))),
          'column_cell' => new \Twig_Function_Method($this, 'renderColumnCell', 
          array('is_safe' => array('html'))),
          'column_header' => new \Twig_Function_Method($this, 'renderColumnHeader', 
          array('is_safe' => array('html')))
        );
    }

    /**
     * Renders a table.
     *
     * @param Table The Table instance to render.
     */
    public function renderTableWidget(Table $table)
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
    public function renderColumnCell(ColumnCell $cell, $value)
    {
        return $this->container->get('templating')->render(
            'TacticsTableBundle::column_cell_'.$cell->getType().'.html.twig',
            array('cell' => $cell, 'value' => $value)
          );
    }

    /**
     * Renders a ColumnHeader.
     * 
     * @param ColumnHeader The ColumnHeader instance to render.
     */
    public function renderColumnHeader(ColumnHeader $header)
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
