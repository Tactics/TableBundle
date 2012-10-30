<?php

namespace Tactics\TableBundle\Extension\Builder;

use Tactics\TableBundle\TableBuilder;
use Tactics\TableBundle\TableFactoryInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DoctrineTableBuilder extends TableBuilder
{
    protected $columns = array();

    /**
     * @var $queryBuilder Doctrine\ORM\QueryBuilder A QueryBuilder instance.
     */
    protected $queryBuilder = null;

    /**
     * Namespace used by \Tactics\Bundle\TableBundle\ModelCriteriaFilter\ModelCriteriaSorter
     *
     * @var $sorterNamespace string The sorter namespace.
     */
    protected $sorterNamespace = null;


    /**
     * @inheritDoc
     */
    public function __construct($name, $type = '', TableFactoryInterface $factory, array $options = array())
    {
        parent::__construct($name, $type, $factory, $options);

        $this->queryBuilder = $this->options['query_builder'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setRequired(array('query_builder'));
    }
}
