<?php

namespace Tactics\TableBundle\Extension\Builder;

use Tactics\TableBundle\TableBuilder;
use Tactics\TableBundle\TableFactoryInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\Common\Util\Inflector;

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
                // @tododoctrine
                /*$headerType = 'sortable';*/
                $headerType = 'text';
            }

            /*if ('sortable' === $headerType && $this->getSorterNamespace()) {
                $options['header/sorter_namespace'] = $this->getSorterNamespace();
            }*/

            // Guess column header value (title)
            if (! isset($options['header/value'])) {
                $options['header/value'] = ucfirst(strtolower(str_replace('_', ' ', $name)));
            }

            // Guess sort order from model criteria
            // @tododoctrine
            /*if (! isset($options['header/sort'])) {
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
            }*/

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
            
            if (false !== array_key_exists($name, $this->getAssociationMappings())) {
                $type = 'foreign_key';
            }
            
            // guess foreign_key options
            if ($type == 'foreign_key') {
                die('Call Aaron, he needs to fix this part now that there is a use case.');
                $mapping = $this->getAssociationMapping($name);
                // @todo fix protected $cmd var.
                // @todo support collection valued associations.
                $cmd = $this->getClassMetaData();
                if (! $cmd->isSingleValuedAssociation($name)) {
                    throw new \Exception('Only single value associations are supported at the moment.');
                }


                if (! isset($options['column/route'])) {
                    $container = $this->getTableFactory()->getContainer();
                    $routeResolver = $container->get('tactics.object_route_resolver');

                    $options['column/route'] = array(
                        $routeResolver->retrieveByClass($mapping['targetEntity']),
                        array('id' => $name)
                    );
                }

                if (! isset($options['column/target_entity'])) {
                    $options['column/target_entity'] = $mapping['targetEntity'];
                }
            }

            $mapping = $this->getFieldMapping($name);

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
        /*$factory = $this->getTableFactory();
        $request = $factory->getContainer()->get('request');
        $orderBy = $request->get('order_by');*/

        // todo
        // At time of testing, a new table was made each request.
        // Need to find a way to store table settings into session.
        /*if ($orderBy)
        {
            $column = $table->offsetGet($orderBy);
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
        }*/

        foreach ($this->queryBuilder->getQuery()->getResult() as $object) {
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
     * Translate field name to method.
     *
     * @param $fieldName string
     * @return string
     */
    private function translateFieldNameToMethod($fieldName)
    {
        if (array_search($fieldName, $this->getAllFieldNames()) === false) {
            throw new \Exception('Unknown field name '.$fieldName);
        }

        return 'get'.Inflector::camelize($fieldName);
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
