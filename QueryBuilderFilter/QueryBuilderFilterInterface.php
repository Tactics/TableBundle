<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

interface QueryBuilderFilterInterface extends ContainerAwareInterface
{
     /**
      * @param QueryBuilder  $qb  A QueryBuilder instance.
      * @param string        $key The key under which settings will be saved.
      * @param array         $options 
      */
     function execute(QueryBuilder $qb, $key = null, $options = array());
}
