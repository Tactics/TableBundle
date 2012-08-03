<?php

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class ActionsColumn extends Column
{
    /**
     * @var $actions array The actions displayed in this column.
     */
    protected $actions = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($name, ColumnHeader $header, array $options = array())
    {
        parent::__construct($name, $header, $options);

        $actionsResolver = new OptionsResolver();
        $this->setDefaultActionsOptions($actionsResolver);

        // todo nested resolvers?
        foreach ($this->options['actions'] as $action => $options) {
            $this->actions[$action] = $actionsResolver->resolve($options);
        }
    }

    /**
     * @return array The actions.
     */
    public function getActions() 
    {
        return $this->actions;
    }

    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired(array('actions'));
    }

    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultActionsOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('icon', 'label', 'route'));
        $resolver->setOptional(array('route_param'));
    }

    /**
     * {@inheritdoc}
     */ 
    public function getType()
    {
        return 'actions';
    }
}
