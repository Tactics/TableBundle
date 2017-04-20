<?php

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;
use Tactics\TableBundle\Exception\TableException;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class ActionsColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name, ColumnHeader $header, array $options = array(), $extensions = array())
    {
        parent::__construct($name, $header, $options);

        $actionsResolver = new OptionsResolver();
        $this->setDefaultActionsOptions($actionsResolver);

        // todo nested resolvers?
        foreach ($this->options['actions'] as $action => &$options) {
            $options = $actionsResolver->resolve($options);
        }

        // todo with css parser
        // $css = new HTML_CSS();
        // $css->parseString($this->getHeader()->getOption('attributes'));
        // $css->setStyle('width', (count($this->actions) * 25) . 'px');
        // $this->getHeader()->setOption('attributes', $css->toInline());


        $headerAttributes = $this->getHeader()->getOption('attributes');
        $headerAttributes = $headerAttributes ? $headerAttributes : array();
        $headerAttributes['style'] = (isset($headerAttributes['style']) ? $headerAttributes['style'] : '') . sprintf('; width: %upx;', count($this->options['actions']) * 26);
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
        foreach ($this->options['actions'] as $action => $options) {
            $cell['actions'][$action] = $options;

            switch($action)
            {
                case 'delete':
                    // check isDeletable()
                    if (
                        (! isset($cell['actions'][$action]['disabled']) || ! $cell['actions'][$action]['disabled'])
                        && isset($row['_object']) && method_exists($row['_object'], 'isDeletable')) {

                        if (! $row['_object']->isDeletable()) {
                            $cell['actions'][$action]['disabled'] = true;
                            $className = strtolower(join('', array_slice(explode('\\', get_class($row['_object'])), -1)));
                            $cell['actions'][$action]['title'] = 'This ' . $className . ' cannot be deleted.';
                        }
                    }

                    // default icon
                    if (! isset($cell['actions'][$action]['icon']))
                    {
                        $cell['actions'][$action]['icon'] = 'fa fa-trash-o';
                    }

                    break;

                case 'show':
                    // default icon
                    if (! isset($cell['actions'][$action]['icon']))
                    {
                        $cell['actions'][$action]['icon'] = 'fa fa-search';
                    }

                    break;

                case 'edit':
                    // default icon
                    if (! isset($cell['actions'][$action]['icon'])) {
                        $cell['actions'][$action]['icon'] = 'fa fa-edit';
                    }

                    break;

            }

            // Default action title
            if (! isset($cell['actions'][$action]['title']))
            {
                $cell['actions'][$action]['title'] = ucfirst($action);
            }

            // Default action icon
            if (! isset($cell['actions'][$action]['icon']))
            {
                $cell['actions'][$action]['icon'] = $action;
            }

            if (isset($cell['actions'][$action]['disabled']))
            {
                $disabled = $cell['actions'][$action]['disabled'];

                if (is_callable($disabled)) {
                    if ($disabled($row)) {
                        $cell['actions'][$action]['attributes']['class'] = trim(
                            $cell['actions'][$action]['attributes']['class'] .
                            ' ' .
                            'disabled'
                        );
                    }
                } elseif ($disabled) { // BC, deprecated.
                    $cell['actions'][$action]['attributes']['class'] = trim(
                        $cell['actions'][$action]['attributes']['class'] .
                        ' ' .
                        'disabled'
                    );
                }
            }

            if (isset($cell['actions'][$action]['enabled_if'])){
                $method = $cell['actions'][$action]['enabled_if'];

                if (! $row['_object']->$method()) {
                    $cell['actions'][$action]['attributes']['class'] = trim($cell['actions'][$action]['attributes']['class'] . ' ' . 'disabled');
                    $cell['actions'][$action]['disabled'] = true;
                }
            }

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
                    throw new TableException(sprintf('Could resolve route "%s" in actionsColumn', $options['route'][0]));
                }
            }
            else
            {
                throw new TableException('Invalid route configuration for action in actionsColumn.');
            }
        }

        return $cell;
    }

    /**
     * @return array The actions.
     */
    public function getActions()
    {
        return $this->options['actions'];
    }

    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(array('actions'));
    }

    /**
     * Sets the default options for this table.
     *
     * todo nested resolvers?
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function setDefaultActionsOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('route'));

        $resolver->setDefined(array(
            'route_param',
            'disabled',
            'enabled_if',
            'attributes',
            'icon',
            'title',
            'required_roles'
        ));

        $resolver->setAllowedTypes(
            'required_roles' , 'array'
        );

        $resolver->setDefaults(array(
            'attributes' => array('style' => 'text-align: center;', 'class' => ''),
            'disabled' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'actions';
    }
}
