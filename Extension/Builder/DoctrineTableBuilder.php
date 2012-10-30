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
        return array_merge($this->getFieldNames(), array_keys($this->getAssociationMappings()));
    }

    /**
     * @inheritDoc
     */
    public function create($name, $type = null, $headerType = null, array $options = array())
    {
        // do guess work if name is a db field name
        if (false !== array_search($name, $this->getAllFieldNames())) {
            // Default header type: sortable
            if (! $headerType) {
                $headerType = 'sortable';
            }

            if ('sortable' === $headerType && $this->getSorterNamespace()) {
                $options['header/sorter_namespace'] = $this->getSorterNamespace();
            }

            // Guess column header value (title)
            if (! isset($options['header/value'])) {
                $options['header/value'] = ucfirst(strtolower(str_replace('_', ' ', substr($name, (strpos($name, '.')+1), strlen($name)))));
            }

            // Guess sort order from model criteria
            if (! isset($options['header/sort'])) {
                foreach ($this->modelCriteria->getOrderByColumns() as $orderByColumn) {
                    if (strpos($orderByColumn, $name) !== false) {
                        // Find out which sort is applied
                        if (strpos($orderByColumn, Criteria::ASC)) {
                            $options['header/sort'] = SortableColumnHeader::ASC;
                        } else {
                            $options['header/sort'] = SortableColumnHeader::DESC;
                        }

                        break;
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
                $options['column/method'] = $this->translateColnameToMethod($name);
            }

            // Retrieve TableMap column by name.
            $rawColName = $this->objectPeer->translateFieldname(
                $name,
                \BasePeer::TYPE_COLNAME,
                \BasePeer::TYPE_RAW_COLNAME
            );

            $column = $this->tableMap->getColumn($rawColName);

            // guess foreign_key type
            if (! $type && (true === $column->isForeignKey())) {
                $type = 'foreign_key';
            }

            // guess foreign_key options
            if ($type == 'foreign_key') {
                $foreignTable = $column->getRelation()->getForeignTable();

                if (! isset($options['column/route'])) {
                    $container = $this->getTableFactory()->getContainer();
                    $routeResolver = $container->get('tactics.object_route_resolver');

                    $options['column/route'] = array(
                        $routeResolver->retrieveByClass($foreignTable->getClassname()),
                        array('id' => $name)
                    );
                }

                if (! isset($options['column/foreign_table'])) {
                    $options['column/foreign_table'] = $foreignTable;
                }

                // fix header title by removing "id" suffix
                if ((substr($options['header/value'], -3) == ' id') && strlen($options['header/value']) > 3) {
                    $options['header/value'] = substr($options['header/value'], 0, -2);
                }
            }

            // guess datetime type
            if (! $type && in_array($column->getType(), array('DATE', 'TIME', 'TIMESTAMP') )) {
                $type = 'date_time';
            }

            // guess datetime options
            if ($type == 'date_time') {

                if (! isset($options['column/show_time']) && ($type == 'date_time') && ('DATE' == $column->getType())) {
                    $options['column/show_time'] = false;
                }

                if (! isset($options['column/show_time']) && ($type == 'date_time') && ('TIME' == $column->getType())) {
                    $options['column/show_date'] = false;
                }
            }

            // guess email type
            if (! $type && ($rawColName == 'EMAIL')) {
                $type = 'email';
            }

            // guess array type
            if (! $type && ($column->getType() == 'ARRAY')) {
                $type = 'array';
            }

            // guess boolean type
            if (! $type && ($column->getType() == 'BOOLEAN')) {
                $type = 'boolean';
            }
        }

        return parent::create($name, $type, $headerType, $options);
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
