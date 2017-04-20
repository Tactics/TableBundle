<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QueryBuilderSorter implements QueryBuilderFilterInterface
{
    /**
      * @var $container ContainerInterface A ContainerInterface instance.
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null) 
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(QueryBuilder $qb, $key = null, $options = array())
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $session = $this->container->get('session');

        // Retrieve sorts from session.
        $key = null === $key ? 'sorter/'.$request->attributes->get('_route') : $key;

        $sorts = $session->has($key) ? $session->get($key) : array();

        if ($request->get('sorter_namespace') && $request->get('sorter_namespace') !== $key) {
            // Nothing.
        } else {
            // Retrieve sort from request.
            // Create, update or delete sort from current sorts.
            if ($request->get('asc')) {
                $sorts = $this->sort($this->addAlias($qb, $request->get('asc')), 'ASC', $sorts);        
            } elseif ($request->get('desc')) {
                $sorts = $this->sort($this->addAlias($qb, $request->get('desc')), 'DESC', $sorts);
            } elseif ($request->get('unsort')) {
                $sorts = $this->unsort($this->addAlias($qb, $request->get('unsort')), $sorts);
            } 
        }

        $defaultSortingApplied = false;
        //When default exist -> apply it (if not in session and not applied by the request). But do not store it in the session
        if (isset($options['default_sort']) && $options['default_sort'] && !$this->findKeyByName($options['default_sort']['name'], $sorts)) {
            $defaultSortingApplied = true;
            $sorts = $this->sort(
                $this->addAlias($qb, $options['default_sort']['name']),
                $options['default_sort']['asc_or_desc'],
                $sorts
            );
        }

        // Add sorts to QueryBuilder.
        foreach ($sorts as $sortskey => $sort) {
            $qb->addOrderBy($sort['name'], $sort['asc_or_desc']);
            //We applied the default sorting so it was not already in the session or applied by the user, unset it from sorts before saving sorts in session
            if($defaultSortingApplied && $sort['name'] = $options['default_sort']['name']) {
                unset($sorts[$sortskey]);
            }
        }

        // Set updated sorts in session.
        $session->set($key, $sorts);
        
        return $qb;
    }

    private function addAlias(QueryBuilder $qb, $fieldName)
    {
        // @todo support multiply entities.
        $aliases = $qb->getRootAliases();
        $alias = $aliases[0];

        return $alias.'.'.$fieldName;
    }

    private function sort($name, $ascOrDesc, $sorts) 
    {
        if (($key = $this->findKeyByName($name, $sorts)) !== false) {
            $sorts[$key]['asc_or_desc'] = $ascOrDesc; 
        } else {
            $sorts[] = array('name' => $name, 'asc_or_desc' => $ascOrDesc);
        }

        return $sorts;
    }

    private function unsort($name, $sorts)
    {
        if (($key = $this->findKeyByName($name, $sorts)) !== false) {
            unset($sorts[$key]);
        }

        return $sorts;
    }

    private function findKeyByName($name, $sorts)
    {
        foreach ($sorts as $key => $sort) {
            if ($sort['name'] === $name) return $key; 
        }

        return false;
    }
}
