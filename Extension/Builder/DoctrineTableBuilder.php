<?php

namespace Tactics\TableBundle\Extension\Builder;

use Tactics\TableBundle\TableBuilder;
use Tactics\TableBundle\TableFactoryInterface;
use Tactics\TableBundle\Extension\Type\SortableColumnHeader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\Common\Util\Inflector;

class DoctrineTableBuilder extends TableBuilder
{
    protected $columns = array();

    /**
     * @var $query Doctrine\ORM\Query A Query instance.
     */
    protected $query = null;

    /**
     * @var $repository Doctrine\ORM\EntityRepository An EntityRepository 
     * instance.
     */
    protected $repository = null;

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

        $this->repository = $this->options['repository'];
        $this->query      = $this->options['query'];
    }

    /**
     * $this->query can be a Pagerfanta instance or a Query instance.
     * 
     * @return Doctrine\ORM\Query
     */
    public function getQuery()
    {
        return ('Pagerfanta\\Pagerfanta' === get_class($this->query)) ? 
            $this->query->getAdapter()->getQuery() : $this->query->getResult();
    }

    /**
     * Sets namespace used by \Tactics\Bundle\TableBundle\ModelCriteriaFilter\ModelCriteriaSorter
     *
     * @param $v string The sorter namespace.
     */
    public function setSorterNamespace($v)
    {
       $this->sorterNamespace = $v; 

       return $this;
    }

    /**
     * Retrieves namespace used by \Tactics\Bundle\TableBundle\ModelCriteriaFilter\ModelCriteriaSorter
     *
     * @return string The sorter namespace.
     */
    public function getSorterNamespace()
    {
        return $this->sorterNamespace;
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
        foreach (array_diff($this->getAllFieldNames(), $exclude) as $fieldName) {
            $this->add($fieldName);
        }

        return $this;
    }

    /**
     * Proxy method for Doctrine\ORM\Mapping\ClassMetadataInfo::getFieldNames
     *
     * @return array Fieldnames.
     */
    private function getFieldNames()
    {
        $cmd = $this->getClassMetaData();

        return $cmd->getFieldNames();
    }

    /**
     * Proxy method for Doctrine\ORM\Mapping\ClassMetadataInfo::getFieldMapping
     *
     * @return array Field mapping.
     */
    private function getFieldMapping($fieldName)
    {
        $cmd = $this->getClassMetaData();

        return $cmd->getFieldMapping($fieldName);
    }

    /**
     * Proxy method for Doctrine\ORM\Mapping\ClassMetadataInfo::getAssociationMappings
     *
     * @return array AssociationMappings.
     */
    private function getAssociationMappings()
    {
        $cmd = $this->getClassMetaData();

        return $cmd->getAssociationMappings();
    }


    /**
     * Proxy method for Doctrine\ORM\Mapping\ClassMetadataInfo::getAssociationMapping
     *
     * @return array AssociationMapping.
     */
    private function getAssociationMapping($fieldName)
    {
        $cmd = $this->getClassMetaData();

        return $cmd->getAssociationMapping($fieldName);
    }

    /**
     * Technically incorrect because associations are not fields.
     * Reason this exists is because I focussed on refactoring the 
     * PropelTableBuilder.
     *
     * Will create an array with all association names and merge it with field 
     * names.
     *
     * @return array
     */
    private function getAllFieldNames()
    {
        return array_merge($this->getFieldNames(), $this->getAssociationMappingNames());
    }

    /**
     * Returns tableized version of association mapping names.
     *
     * @return array
     */
    private function getAssociationMappingNames()
    {
        $names = array_keys($this->getAssociationMappings());
        array_walk($names, function(&$value, &$key) {
            $value = Inflector::tableize($value);
        });

        return $names;
    }

    /**
     * @inheritDoc
     */
    public function create($name, $type = null, $headerType = null, array $options = array())
    {
        if (false !== array_search($name, $this->getAllFieldNames())) {
            if (false !== array_search($name, $this->getFieldNames())) {
                $mapping = $this->getFieldMapping($name);
            } elseif (false !== array_search($name, $this->getAssociationMappingNames())) {
                $type = 'association';
                $name = Inflector::camelize($name);
                $mapping = $this->getAssociationMapping($name);
            }

            if (! $headerType) {
                $headerType = 'sortable';
            }

            if ('sortable' === $headerType && $this->getSorterNamespace()) {
                $options['header/sorter_namespace'] = $this->getSorterNamespace();
            }

            if (! isset($options['header/value'])) {
                $options['header/value'] = ucfirst(strtolower(str_replace('_', ' ', $name)));
            }

            if (! isset($options['header/sort'])) {
                $selectStmt = $this->getQuery()->getAST();

                if ($selectStmt->orderByClause) {
                    foreach ($selectStmt->orderByClause->orderByItems as $orderByItem) {
                        $expr = $orderByItem->expression;
                        if ($expr->identificationVariable.'.'.$name === $expr->identificationVariable.'.'.$expr->field) {
                            if ($orderByItem->isAsc()) {
                                $options['header/sort'] = SortableColumnHeader::ASC;
                            } else {
                                $options['header/sort'] = SortableColumnHeader::DESC;
                            }

                            break;
                        }
                    }
                }
            }

            // todo ColumnHeader extensions should fix this.
            // todo , this is temp fix for _internal problem when using table in render subrequests
            // @see https://github.com/Tactics/TableBundle/issues/10
            $router = $this->getTableFactory()->getContainer()->get("router");
            $route = $router->match($this->getTableFactory()->getContainer()->get('request')->getPathInfo());

            $routeParams = $route;
            unset($routeParams['_controller']);
            unset($routeParams['_route']);

            $options['header/route'] = $route['_route'];
            $options['header/route_params'] = $routeParams;

            // OLD WAY, does not work for sub requests: always returns '_internal'
            // $request = $this->getTableFactory()->getContainer()->get('request');
            // $route = $request->attributes->get('_route');
            // $options['header/route'] = $route;
            // $options['header/route_params'] = $request->attributes->get('_route_params') ? $request->attributes->get('_route_params') : array();

            // getMethod throws exception when method is not found.
            if (! isset($options['column/method'])) {
                $options['column/method'] = $this->translateFieldNameToMethod($name);
            }

            // guess datetime type
            if (! $type && in_array($mapping['type'], array('date', 'time', 'datetime') )) {
                $type = 'date_time';
            }

            // guess datetime options
            if ($type == 'date_time') {

                if (! isset($options['column/show_time']) && ($type == 'date_time') && ('date' == $mapping['type'])) {
                    $options['column/show_time'] = false;
                }

                if (! isset($options['column/show_time']) && ($type == 'date_time') && ('time' == $mapping['type'])) {
                    $options['column/show_date'] = false;
                }
            }

            // guess email type
            if (! $type && ($name == 'email')) {
                $type = 'email';
            }

            // guess array type
            if (! $type && ($mapping['type'] == 'array')) {
                $type = 'array';
            }

            // guess boolean type
            if (! $type && ($mapping['type'] == 'boolean')) {
                $type = 'boolean';
            }
        }

        return parent::create($name, $type, $headerType, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($options = array())
    {
        // todo: create!
        $table = $this->factory->createTable($this->name, $this->type, $options);

        foreach ($this as $column) {
            $table->add($column);
        }

        // todo
        // All of this is a bit weird since we don't really know we're dealing
        // with sortable columns, well, I know, but ..
        $factory = $this->getTableFactory();
        $request = $factory->getContainer()->get('request');
        $orderBy = $request->get('order_by');

        // todo
        // At time of testing, a new table was made each request.
        // Need to find a way to store table settings into session.
        if ($orderBy)
        {
            /**
             * @var $idvc array An array containing Doctrine\ORM\Query\AST\IdentificationVariableDeclaration instances.
             */
            $idvcs = $this->getQuery()->getAST()->fromClause->identificationVariableDeclarations;

            // @todo support multiple entities in one table.
            $idvc = $idvcs[0];
            $aliasIdentificationVariable = $idvc->rangeVariableDeclaration->aliasIdentificationVariable;

            $column = $table->offsetGet($aliasIdentificationVariable.'.'.$orderBy);
            $header = $column->getHeader();

            switch ($header->getState()) {
                case SortableColumnHeader::ASC:
                    $header->setState(SortableColumnHeader::DESC);
                    $this->modelCriteria->orderBy($orderBy, Criteria::DESC);
                    break;
                case SortableColumnHeader::DESC:
                    $header->setState(SortableColumnHeader::NO_SORT);
                    break;
                default:
                    $header->setState(SortableColumnHeader::ASC);
                    $this->modelCriteria->orderBy($orderBy, Criteria::ASC);
                    break;
            }
        }

        $results = ('Pagerfanta\\Pagerfanta' === get_class($this->query)) ? 
            $this->query : $this->query->getResult();

        $rows = array();
        foreach ($results as $object) {
            $rowArr = array('_object' => $object);
            foreach ($table as $column) {
                $options = $column->getOptions();

                // get value from object if method defined
                if (! isset($options['method'])) {
                    $rowArr[$column->getName()] = array('value' => null);
                }
                else {
                    $method  = $options['method'];
                    $rowArr[$column->getName()] = array('value' => $object->$method());
                }

                // default value
                if (($rowArr[$column->getName()]['value'] === null) &&  isset($options['default_value'])) {
                    $rowArr[$column->getName()]['value'] = $options['default_value'];
                }
            }

            $rows[] = $rowArr;
        }

        $table->setRows($rows);

        return $table;
    }

    /**
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getClassMetaData()
    {
        $container = $this->getTableFactory()->getContainer();
        $cmf = $container->get('doctrine')->getEntityManager()->getMetadataFactory();

        return $cmf->getMetadataFor($this->repository->getClassName());
    }

    /**
     * Translate field name to method.
     *
     * @param $fieldName string
     * @return string
     */
    private function translateFieldNameToMethod($fieldName)
    {
        if (array_search(Inflector::tableize($fieldName), $this->getAllFieldNames()) === false) {            
            throw new \Exception('Unknown field name '.$fieldName);
        }

        return Inflector::camelize('get' . $fieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setRequired(array('repository', 'query'));
    }
}
