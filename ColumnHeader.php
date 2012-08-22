<?php

namespace Tactics\TableBundle;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ColumnHeader implements ColumnHeaderInterface
{
    /**
     * @var $value String
     */
    protected $value;

    /**
     *  @var $column ColumnInterface
     */
    protected $column;

    /*
     * @var $options array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function __construct($value, array $options = array())
    {
        $this->value      = $value;

        $resolver = new OptionsResolver(); 
        $this->setDefaultOptions($resolver);

        $this->options = $options;
        $this->options = $resolver->resolve($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    

    public function getOptions()
    {
        return $this->options;
    }
    
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setColumn(ColumnInterface $column)
    {
        $this->column = $column;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'default';
    }

    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
          ->setOptional(array('route', 'type', 'value', 'sort', 'route_params', 'attributes'))
          ->setDefaults(array('attributes' => array()));
    }
}
