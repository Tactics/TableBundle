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
     * @var $attributes array
     */
    protected $attributes;

    /*
     * @var $attributes array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function __construct($value, array $attributes = array(), array $options = array())
    {
        $this->value      = $value;
        $this->attributes = $attributes;

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

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setColumn(ColumnInterface $column)
    {
        $this->column = $column;
    }

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
        // todo lose the resolver, create attributes.
        $resolver->setOptional(array('route', 'type', 'value', 'sort', 'route_params'));
    }
}
