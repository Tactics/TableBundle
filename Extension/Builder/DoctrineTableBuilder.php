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
     * Retrieves all the fieldnames from query builder and adds them.
     *
     * @param array $exclude Names of fields to exclude.
     *
     * @return DoctrineTableBuilder $this The DoctrineTableBuilder instance.
     */
    public function addAll(array $exclude = array())
    {
        $cmd = $this->getClassMetaData();

        $fieldNames = array_diff($cmd->getFieldnames(), $exclude);
        $associationNames = array_diff(array_keys($cmd->getAssociationMappings()), $exclude);

        foreach (array_merge($fieldNames, $associationNames) as $fieldName)
        {
            $this->add($fieldName);
        }

        return $this;
    }

    /**
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getClassMetaData()
    {
        $entityClassNames = $this->queryBuilder->getRootEntities();

        if (! $entityClassNames) {
            throw new \Exception('Unable to retrieve root entities from QueryBuilder.');
        }

        // @todo: support multiple entities in one table.
        $entityClassName = $entityClassNames[0];

        $cmf = $this->queryBuilder->getEntityManager()->getMetadataFactory();

        if (! $cmf->hasMetaDataFor($entityClassName)) {
            throw new \Exception('Unable to retrieve ClassMetaData for "'.$entityClassName.'".');
        }

        return $cmf->getMetaDataFor($entityClassName);
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
