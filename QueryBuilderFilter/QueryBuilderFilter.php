<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tactics\TableBundle\Form\Type\QueryBuilderFilterType;
use Tactics\myDate\myDate;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Comparison;

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
    public function execute(QueryBuilder $qb, $key = null, $options = array())
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
        
        // Translate filter info into a QuiryBuilder query.
        foreach ($this->fields as $fieldName => $options) {
            if (isset($options['filter']) && $this->get($fieldName)) {
                $options['filter']($qb, $this->getAlias($qb), $fieldName, $this->get($fieldName));
            } else {
                if($options['type'] === 'entity') {
                    $options['comparison'] = '=';
                }
                if (($options['type'] === 'date') || ($options['type'] === 'datum')) {
                  
                  $value = $this->get($fieldName, '_from');
                  if ($value)
                  {
                      $dt = \DateTime::createFromFormat('d/m/Y', $value);
                      $qb->andWhere(
                          $qb->expr()->gte(
                              $this->getAlias($qb, $fieldName),
                              ':'.$fieldName.'_from'
                          ))
                          ->setParameter($fieldName.'_from', $dt);
                  }
                  
                  $value = $this->get($fieldName, '_to');
                  if ($value)
                  {
                      $dt = \DateTime::createFromFormat('d/m/Y', $value);
                      $qb->andWhere(
                          $qb->expr()->lte(
                              $this->getAlias($qb, $fieldName),
                              ':'.$fieldName.'_to'
                          ))
                          ->setParameter($fieldName.'_to', $dt);
                  }
                }
                else
                {
                    $value = $this->get($fieldName);
                    
                    if (null !== $value && '' !== $value) {
                        if (! isset($options['comparison'])) {
                            $qb->andWhere(
                                $qb->expr()->eq(
                                    $this->getAlias($qb, $fieldName),
                                    ':'.$fieldName
                                )
                            );
                        } else {
                            switch ($options['comparison']) {
                                case 'LIKE':
                                    $qb->andWhere(
                                        $qb->expr()->like(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case '=':
                                    $qb->andWhere(
                                        $qb->expr()->eq(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case '<>':
                                    $qb->andWhere(
                                        $qb->expr()->neq(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case '<':
                                    $qb->andWhere(
                                        $qb->expr()->lt(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case '<=':
                                    $qb->andWhere(
                                        $qb->expr()->lte(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case '>':
                                    $qb->andWhere(
                                        $qb->expr()->gt(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case '>=':
                                    $qb->andWhere(
                                        $qb->expr()->gte(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case 'IS NULL':
                                    $qb->andWhere(
                                        $qb->expr()->isNull(
                                            $this->getAlias($qb, $fieldName)
                                        )
                                    );
                                    break;
                                case 'IS NOT NULL':
                                    $qb->andWhere(
                                        $qb->expr()->isNotNull(
                                            $this->getAlias($qb, $fieldName)
                                        )
                                    );
                                    break;
                                case 'IN':
                                    $qb->andWhere(
                                        $qb->expr()->in(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                case 'NOT IN':
                                    $qb->andWhere(
                                        $qb->expr()->notIn(
                                            $this->getAlias($qb, $fieldName),
                                            ':'.$fieldName
                                        )
                                    );
                                    break;
                                  case 'INSTANCE OF':
                                    $qb->andWhere(
                                        $this->getAlias($qb) . ' INSTANCE OF ' . $value
                                    );
                                    break;
                                default:
                                    throw new \Exception('Unsupported comparison '.$options['comparison']);
                                    break;
                            }
                        }

                        if (isset($options['comparison']) && 'LIKE' === $options['comparison']) {
                            $qb->setParameter($fieldName, '%'.$value.'%');
                        } elseif (isset($options['comparison']) && 'INSTANCE OF' === $options['comparison']) {
                            // Nothing
                        } elseif (! isset($options['comparison']) || 'IS NULL' !== $options['comparison'] && 'IS NOT NULL' !== $options['comparison']) {
                            $qb->setParameter($fieldName, $value);
                        }
                    }
            }
            }
        }
        return $qb;
    }

    private function getAlias(QueryBuilder $qb, $fieldName = null)
    {
        // @todo support multiply entities.
        $aliases = $qb->getRootAliases();
        $alias = $aliases[0];

        return $fieldName ? $alias . '.' . $fieldName : $alias;
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
     * @return $this QueryBuilderFilter The QueryBuilder instance.
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
            ->createBuilder(new QueryBuilderFilterType());

        foreach ($this->fields as $fieldName => $options)
        {
            $value = isset($this->values[$fieldName]) ? $this->values[$fieldName] : null;
            
            $fieldOptions = array(
                'required' => false,
                'data' => $value,
                'label' => $options['label'],
                'render_optional_text' => false,
            );
            
            $formFieldName = $options['form_field_name'];
            
            // Prepare
            switch($options['type'])
            {
                case 'date':
                case 'datum':
                    if ($options['datum_from_and_to']){
                        $fieldOptions['data'] = $value ? \DateTime::createFromFormat('d/m/Y', $value) : null;
                        $fieldOptions['label'] = $options['label'] . ' from';
                        $builder->add($formFieldName . '_from', $options['type'], $fieldOptions);
                        $fieldOptions['label'] = $options['label'] . ' to';
                        $builder->add($formFieldName . '_to', $options['type'], $fieldOptions);
                        break;    
                    }
                    else {
                        $fieldOptions['data'] = $value ? \DateTime::createFromFormat('d/m/Y', $value) : null;
                        $builder->add($formFieldName, $options['type'], $fieldOptions);
                        break;
                    }
                case 'choice':
                    $fieldOptions['choices'] = $options['choices'];
                    $builder->add($formFieldName, $options['type'], $fieldOptions);
                    break;
                case 'boolean':
                    $options['type'] = 'choice';
                    $fieldOptions['choices'] = array(0 => 'No', 1 => 'Yes');
                    $builder->add($formFieldName, $options['type'], $fieldOptions);
                    break;
                case 'entity':
                    $fieldOptions['class'] = $options['class'];
                    $fieldOptions['query_builder'] = $options['query_builder'];
                    $builder->add($formFieldName, $options['type'], $fieldOptions);
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
                'comparison' => 'LIKE',
                'type'     => 'text',
                'value'    => null,
                'choices'  => null,
                'class' => null,
                'query_builder' => null,
                'datum_from_and_to' => true
        ));

        $resolver->setOptional(array('label', 'form_field_name', 'filter'));
    }
    
    public function buildFromType(QueryBuilderFilterTypeInterface $type)
    {
        $type->build($this);
    }
}
