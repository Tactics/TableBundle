<?php

namespace Tactics\TableBundle\ModelCriteriaFilter;

use ModelCriteria;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

interface ModelCriteriaFilterInterface extends ContainerAwareInterface
{
     /**
      * @param ModelCriteria $mc  A ModelCriteria instance.
      * @param string        $key The key under which settings will be saved.
      * @param array         $options 
      */
     function execute(ModelCriteria $mc, $key = null, $options = array());
}
