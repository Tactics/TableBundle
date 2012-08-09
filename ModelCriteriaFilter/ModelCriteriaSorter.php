<?php

namespace Tactics\TableBundle\ModelCriteriaFilter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use \ModelCriteria;
use \Criteria;

class ModelCriteriaSorter implements ModelCriteriaFilterInterface
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function execute(ModelCriteria $mc, $key = null, $options = array())
    {
        $request = $this->container->get('request');
        $session = $this->container->get('session');

        // Retrieve sorts from session.
        $key = null === $key ? 'sorter/'.$request->attributes->get('_route') : $key;

        $sorts = $session->has($key) ? $session->get($key) : array();
        
        // Retrieve sort from request.
        // Create, update or delete sort from current sorts.
        if ($request->get('asc')) {
            $sorts = $this->sort($request->get('asc'), Criteria::ASC, $sorts);        
        } elseif ($request->get('desc')) {
            $sorts = $this->sort($request->get('desc'), Criteria::DESC, $sorts);
        } elseif ($request->get('unsort')) {
            $sorts = $this->unsort($request->get('unsort'), $sorts);
        } 

        // Add sorts to ModelCriteria.
        foreach ($sorts as $sort) {
            $mc->orderBy($sort['name'], $sort['asc_or_desc']);
        } 

        // Set updated sorts in session.
        $session->set($key, $sorts);
        
        return $mc;
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
