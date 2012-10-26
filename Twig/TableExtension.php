<?php

namespace Tactics\TableBundle\Twig;

use Tactics\TableBundle\Table;
use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\ColumnCell;

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
            'render_cell' => new \Twig_Function_Method($this, 'renderCell', 
            array('is_safe' => array('html'))),
            'render_header' => new \Twig_Function_Method($this, 'renderHeader', 
            array('is_safe' => array('html'))),
            'render_attributes' => new \Twig_Function_Method($this, 'renderAttributes', 
            array('is_safe' => array('html'))),
            'table_actions' => new \Twig_Function_Method($this, 'renderActions', 
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
     * @param Column $column The Column instance to render.
     * @param array  $row  An array with the row.
     */
    public function renderCell(Column $column, array $row)
    {  
        if ($column->getOption('hidden'))
        {
            return '';
        }
        
        $cell = $column->getCell($row);
        
        $column->executeExtensions($cell, $row);
        
        return $this->container->get('templating')->render(
            'TacticsTableBundle::column_cell_'.$column->getType().'.html.twig',
            array(
                'column'     => $column, 
                'cell'       => $cell
            )
          );
    }

    /**
     * Renders a ColumnHeader.
     * 
     * @param ColumnHeader The ColumnHeader instance to render.
     */
    public function renderHeader(ColumnHeader $header)
    {
        if ($header->getColumn()->getOption('hidden'))
        {
            return '';
        }
        
        $attributes = '';

        foreach ($header->getOption('attributes') as $attribute => $value) {
            $attributes .= " $attribute=\"$value\"";    
        }

        return $this->container->get('templating')->render(
            'TacticsTableBundle::column_header_'.$header->getType().'.html.twig',
            array(
                'header' => $header
            )
        );
    }

    public function renderAttributes(array $attributes)
    {
        $attributeString = '';

        foreach ($attributes as $attribute => $value) {
            $attributeString .= " $attribute=\"$value\"";    
        }

        return $attributeString;
    }

    public function renderActions()
    {
        return $this->container->get('templating')->render(
            'TacticsTableBundle::table_actions.html.twig'
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
