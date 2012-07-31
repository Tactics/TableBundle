<?php

namespace Tactics\TableBundle\Twig;

use Tactics\TableBundle\Table\Table;

class TableExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'table_widget' => new \Twig_Function_Method($this, 'renderWidget',
            array('is_safe' => array('html'))),
            'table_header' => new \Twig_Function_Method($this, 'renderHeader',
            array('is_safe' => array('html'))),
            'table_rows' => new \Twig_Function_Method($this, 'renderRows',
            array('is_safe' => array('html')))
        );
    }

    /**
     * Renders a Table.
     *
     * @param Table $table A table instance.
     */
    public function renderWidget(Table $table)
    {
        return $this->render($table, 'widget');
    }

    /**
     * Renders the table headers.
     *
     * @param Table $table A Table instance.
     */
    public function renderHeader(Table $table)
    {
        return $this->render($table, 'header');
    }

    /**
     * Renders the table rows.
     *
     * @param Table $table A Table instance.
     */
    public function renderRows(Table $table)
    {
        return $this->render($table, 'rows');
    }
}
