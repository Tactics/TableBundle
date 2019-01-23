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

        $defaultAlias = (isset($options['default_sort']) && $options['default_sort']) ? $this->addAlias($qb, $options['default_sort']['name']) : null;

        if (!$request->get('sorter_namespace') || $request->get('sorter_namespace') === $key) {
            // Retrieve sort from request.
            // Create, update or delete sort from current sorts.
            if ($request->get('asc')) {
                $sorts = $this->sort($this->addAlias($qb, $request->get('asc')), 'ASC', $sorts);        
            } elseif ($request->get('desc')) {
                $sorts = $this->sort($this->addAlias($qb, $request->get('desc')), 'DESC', $sorts);
            } elseif ($request->get('unsort')) {
                $unsort = $this->addAlias($qb, $request->get('unsort'));

                // For default sorts we need to store the state in the session no matter what
                // Otherwise unsorting the default will go back to the default state
                $sorts = $unsort === $defaultAlias
                    ? $this->sort($unsort, 'UNSORT', $sorts)
                    : $this->unsort($unsort, $sorts);
            }
        }

        if ($defaultAlias && ($this->findKeyByName($defaultAlias, $sorts) === false)) {
            $sorts = $this->sort(
                $defaultAlias,
                $options['default_sort']['asc_or_desc'],
                $sorts
            );
        }

        // Add sorts to QueryBuilder.
        foreach ($sorts as $sortskey => $sort) {
            // Don't apply the UNSORT state
            if ($sort['asc_or_desc'] === 'UNSORT') {
                continue;
            }

            $qb->addOrderBy($sort['name'], $sort['asc_or_desc']);
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

        // Prevent double alias
        if (substr($fieldName, 0, 2) === $alias.'.') {
            return $fieldName;
        }

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
