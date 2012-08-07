<?php

namespace Tactics\TableBundle\ModelCriteriaFilter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use \ModelCriteria;

interface ModelCriteriaFilterInterface extends ContainerAwareInterface
{
     /**
      * @param ModelCriteria $mc A ModelCriteria instance.
      * @param string        $key The key under which settings will be saved.
      */
     function execute(ModelCriteria $mc, $key = null);
}
