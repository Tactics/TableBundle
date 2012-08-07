<?php

namespace Tactics\TableBundle\ModelCriteriaFilter;

interface ModelCriteriaFilter
{
    /**
     * Constructor.
     *
     * @param Request       $request   A Request instance.
     * @param ModelCriteria $mc        A ModelCriteria instance.
     * @param string        $namespace The key under which settings will be 
     * saved.
     */
     function __construct($request, $mc, $namespace = null);

     /**
      * @param ModelCriteria $mc A ModelCriteria instance.
      */
     function execute($mc);
}
