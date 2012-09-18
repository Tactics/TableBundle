<?php

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tactics\TableBundle\Exception\TableException;
use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\Extension\Type\LinkColumnExtension;

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
    public function __construct($name, ColumnHeader $header, array $options = array(), $extensions = array())
    {
        parent::__construct($name, $header, $options);

        $actionsResolver = new OptionsResolver();
        $this->setDefaultActionsOptions($actionsResolver);

        // todo nested resolvers?
        foreach ($this->options['actions'] as $action => $options) {
            $this->actions[$action] = $actionsResolver->resolve($options);
        }
        
        // todo with css parser
        // $css = new HTML_CSS();
        // $css->parseString($this->getHeader()->getOption('attributes'));
        // $css->setStyle('width', (count($this->actions) * 25) . 'px');
        // $this->getHeader()->setOption('attributes', $css->toInline());
        
        
        $headerAttributes = $this->getHeader()->getOption('attributes');
        $headerAttributes = $headerAttributes ? $headerAttributes : array();
        $headerAttributes['style'] = (isset($headerAttributes['style']) ? $headerAttributes['style'] : '') . sprintf('; width: %upx;', count($this->actions) * 25);
        $headerAttributes['style'] .= 'text-align: center;';
        $this->getHeader()->setOption('attributes', $headerAttributes);
        
        $attributes = $this->getOption('attributes');
        $attributes = $attributes ? $attributes : array();
        $attributes['style'] = (isset($attributes['style']) ? $attributes['style'] : '') . '; text-align: center;';
        $this->setOption('attributes', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getCell($row)
    {
        $cell = parent::getCell($row);
        
        // todo nested resolvers?
        foreach ($this->options['actions'] as $action => &$options) {
            $cell['actions'][$action] = array();
            
            // BC
            if (isset($options['route_param'])) {
                $cell['actions'][$action]['route_name'] = $options['route'];
                $cell['actions'][$action]['route_params'] = array($options['route_param'] => $cell['value']);
            }
            else if (is_array($options['route'])) {
                $cell['actions'][$action]['route_name'] = $options['route'][0];
                $cell['actions'][$action]['route_params'] = LinkColumnExtension::resolveRouteParameters($options['route'], $row);
                if (! $cell['actions'][$action]['route_params'])
                {
                    throw new TableException(sprintf('Could resolve action route "%s"', $options['route'][0]));
                }
            }
            else
            {
                throw new TableException('Invalid route configuration for action.');
            }
        }
        
        return $cell;
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
     * todo nested resolvers?
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultActionsOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attributes' => array('style' => 'text-align: center;')
        ));
        $resolver->setRequired(array('icon', 'title', 'route'));
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
