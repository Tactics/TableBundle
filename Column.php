<?php

namespace Tactics\TableBundle;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Column implements ColumnInterface
{
    /**
     * @var $header ColumnHeaderInterface
     */
    protected $header;

    /**
     * @var $name string The name of the column.
     */
    protected $name;

    /**
     * @var $options array The column options.
     */
    protected $options;

    /**
     * @var $extensions The type extensions
     */
    protected $extensions;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, ColumnHeader $header, array $options = array(), $extensions = array())
    {
        $this->name = $name;
        $this->header = $header;
        $this->extensions = $extensions;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        foreach($this->extensions as $extension)
        {
            $extension->setDefaultOptions($resolver);
        }

        $this->options = $resolver->resolve($options);

        $this->header->setColumn($this);
    }

    /**
     * @return ColumnHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'default';
    }

    /**
     * {@inheritdoc}
     */
    public function getCell($row)
    {
        $cell = isset($row[$this->getName()]) ? $row[$this->getName()] : array();

        if (! is_array($cell))
        {
            $cell = array('value' => $cell);
        }

        return array_merge(array('value' => null), $cell, $this->getOptions());
    }

    /**
     * {@inheritdoc}
     */
    public function executeExtensions(array &$cell, array &$row)
    {
        foreach($this->extensions as $extension)
        {
            $extension->execute($this, $row, $cell);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // todo: put this stuff somewhere propelTableBuilder related
        $resolver->setOptional(array('method', 'default_value', 'raw', 'hidden'));
        $resolver->setDefaults(array('raw' => false, 'hidden' => false));
    }
}
