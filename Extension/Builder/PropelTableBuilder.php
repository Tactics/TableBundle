<?php

namespace Tactics\TableBundle\Extension\Builder;

use \Criteria;

use Tactics\TableBundle\TableBuilder;
use Tactics\TableBundle\TableFactoryInterface;
use Tactics\TableBundle\Extension\Type\SortableColumnHeader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of PropelTableBuilder
 *
 * @author Gert Vrebos    <gert.vrebos at tactics.be>
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class PropelTableBuilder extends TableBuilder
{
    protected $modelCriteria;
    protected $peerName;
    protected $objectPeer;
    protected $tableMap;
    protected $reflector;
    protected $columns = array();

    /**
     * @inheritDoc
     */
    public function __construct($name, $type = '', TableFactoryInterface $factory, array $options = array())
    {
        parent::__construct($name, $type, $factory, $options);

        $this->modelCriteria = $this->options['model_criteria'];

        $this->peerName = $this->modelCriteria->getModelPeerName();

        $this->tableMap = $this->modelCriteria->getTableMap();

        $this->objectPeer = new $this->peerName();
        $this->reflector  = new \ReflectionClass($this->modelCriteria->getModelName());
    }

    /**
     * Retrieves all the fieldnames from ModelCriteria and adds them.
     * Todo: All fields have to be added, fields can be set to visible or
     * invisible.
     *
     * @param array $exclude Names of fields to exclude.
     *
     * @return PropelTableBuilder $this The PropelTableBuilder instance.
     */
    public function addAll(array $exclude = array())
    {
        foreach (array_diff($this->getFieldnames(), $exclude) as $key => $fieldName) {
            $this->add($fieldName);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function create($name, $type = null, $headerType = null, array $options = array())
    {
        // do guess work if name is a db field name
        if (false !== array_search($name, $this->getFieldnames()))
        {
            // Default header type: sortable
            if (! $headerType)
            {
                $headerType = 'sortable';
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
            $request = $this->getTableFactory()->getContainer()->get('request');
            $route = $request->attributes->get('_route');
            $options['header/route'] = $route;
            $options['header/route_params'] = $request->attributes->get('_route_params') ?
            $request->attributes->get('_route_params') : array();


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
                if ((substr($name, -3) == '_ID') && strlen($name) > 3)
                {
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
     * Proxy for modelPeer::getFieldnames.
     *
     * @return array
     */
    private function getFieldnames()
    {
        return $this->objectPeer->getFieldNames(\BasePeer::TYPE_COLNAME);
    }

    /**
     * Translate raw colname to method.
     *
     * @param $rawColname string
     * @return string
     */
    private function translateColnameToMethod($colname)
    {
        if (array_search($colname, $this->getFieldnames()) === false) {
            throw new \Exception('Unknown column '.$colname);
        }

        return 'get'.$this->objectPeer->translateFieldName(
            $colname,
            \BasePeer::TYPE_COLNAME,
            \BasePeer::TYPE_PHPNAME
        );
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
        }

        $rows = array();

        foreach ($this->modelCriteria->find() as $object) {
            $rowArr = array();
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
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setRequired(array('model_criteria'));
    }
}
