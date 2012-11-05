<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tactics\TableBundle\Form\Type\QueryBuilderFilterType;
use Tactics\myDate\myDate;

class QueryBuilderFilter implements QueryBuilderFilterInterface
{
    /**
     * @var $container ContainerInterface A ContainerInterface instance.
     */
    protected $container;

    /*
     * @var $fields array The filtered fields.
     */
    protected $fields = array();
    
    /**
     * @var $values array The filter values
     */
    protected $values = array();

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ModelCriteria $mc, $key = null, $options = array())
    {
        $request = $this->container->get('request');
        $session = $this->container->get('session');

        $filterBy = $request->get('filter_by');

        $key = null === $key ? 'filter/'.$request->attributes->get('_route') : $key;
        
        // Update fields and place them in the session.
        if ($request->getMethod() == 'POST' && $filterBy) {
            $this->values = $filterBy;
            
            // Store current filter values in session
            $session->set($key, $this->values);
        }
        // User doesn't post, check if filter_by for this route exits in 
        // session.
        else if ($session->has($key)) {
            // Retrieve and validate fields
            $this->values = $session->get($key);
        }
        
        // Add filter info to ModelCriteria.
        foreach ($this->fields as $fieldName => $options) {
            $formFieldName = $options['form_field_name'];
            
            if (($options['type'] === 'date') || ($options['type'] === 'datum')) {
                
              $value = $this->get($fieldName, '_from');
              if ($value)
              {
                  $dt = \DateTime::createFromFormat('d/m/Y', $value);
                  $mc->addAnd($fieldName, $dt, Criteria::GREATER_EQUAL);
              }
              
              $value = $this->get($fieldName, '_to');
              if ($value)
              {
                  $dt = \DateTime::createFromFormat('d/m/Y', $value);
                  $mc->addAnd($fieldName, $dt, Criteria::LESS_EQUAL);
              }
            }
            else
            {
                $value = $this->get($fieldName);
                                
                if ($value) {
                    if ($options['criteria'] === Criteria::LIKE) {
                        $value = '%'.$value.'%';
                    }

                    $mc->addAnd($fieldName, $value, $options['criteria']);
                }
            }
        }
        
        return $mc;
    }
    
    
    /**
     * Returns the current value of the field.
     * 
     * @param type $name
     */
    public function get($name, $suffix = '')
    {
        if (! isset($this->fields[$name]))
        {
            return null;
        }
        
        return isset($this->values[$this->fields[$name]['form_field_name'] . $suffix]) ? $this->values[$this->fields[$name]['form_field_name'] . $suffix] : null;
    }

    /**
     * Adds a field to the fields array.
     *
     * @param $name    string The name of the filter
     * @param $options array  Additional options.
     *
     * @return $this ModelCriteriaFilter The ModelCriteriaFilter instance.
     */
    public function add($name, array $options = array()) 
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $options = $resolver->resolve($options);

        if (! isset($options['form_field_name']))
        {
            // Replace '.' to '__' because '.' is not allowed in a post request.
            $options['form_field_name'] = str_replace('.', '__', $name);
        }
        
        if (! isset($options['label']))
        {
            $label = $name;
            
            // propel field: strip table name
            if (strpos($label, '.'))
            {
                $label = substr($label, strpos($label, '.') + 1);
            }
            
            // propel field: remove _id postfix
            if (strpos($label, '_ID') !== false)
            {
                $label = substr($label, 0, strpos($label, '_ID'));
            }
            
            // humanize
            $label = ucfirst(strtolower(str_replace('_', ' ', $label)));
            
            $options['label'] = $label;
        }
        
        $this->fields[$name] = $options;

        return $this;
    }

    /**
     * Builds a form based on the fields array.
     *
     * @return Form A Form instance.
     */
    public function getForm() 
    {
        $builder = $this->container->get('form.factory')
            ->createBuilder(new ModelCriteriaFilterType());

        foreach ($this->fields as $fieldName => $options)
        {
            $value = isset($this->values[$fieldName]) ? $this->values[$fieldName] : null;
            
            $fieldOptions = array(
                'required' => false,
                'data' => $value,
                'label' => $options['label'],
                'render_optional_text' => false 
            );
            
            $formFieldName = $options['form_field_name'];
            
            // Prepare
            switch($options['type'])
            {
                case 'date':
                case 'datum':
                    $fieldOptions['data'] = $value ? \DateTime::createFromFormat('d/m/Y', $value) : null;
                    $fieldOptions['label'] = $options['label'] . ' from';
                    $builder->add($formFieldName . '_from', $options['type'], $fieldOptions);
                    $fieldOptions['label'] = $options['label'] . ' to';
                    $builder->add($formFieldName . '_to', $options['type'], $fieldOptions);
                    break;
                
                case 'choice':
                    $fieldOptions['choices'] = $options['choices'];
                    $builder->add($formFieldName, $options['type'], $fieldOptions);
                    break;
                
                default:
                    $builder->add($formFieldName, $options['type'], $fieldOptions);
                    break;
            }
        }

        return $builder->getForm();
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    private function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'criteria' => Criteria::LIKE,
                'type'     => 'text',
                'value'    => null,
                'choices'  => null
        ));

        $resolver->setOptional(array('label', 'form_field_name'));
    }
}
