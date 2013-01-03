<?php

namespace Tactics\TableBundle\QueryBuilderFilter;

/**
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
interface QueryBuilderFilterTypeInterface
{
   
    public function build(QueryBuilderFilter $filter);
    
}

