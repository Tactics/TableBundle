<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\ORM\QueryBuilder;

interface QueryBuilderFilterInterface extends ContainerAwareInterface
{
     /**
      * @param QueryBuilder  $qb  A QueryBuilder instance.
      * @param string        $key The key under which settings will be saved.
      * @param array         $options 
      */
     function execute(QueryBuilder $qb, $key = null, $options = array());
}
