<?php

namespace Tactics\TableBundle\Extension\Builder;

use Tactics\TableBundle\Exception\TableException;
use Tactics\TableBundle\TableFactoryInterface;
use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderSorter;
use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderPager;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of TacticsDoctrineTableBuilder
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class TacticsTableBuilder extends DoctrineTableBuilder
{

    protected $sorterFilter;
    protected $pagerFilter;
    protected $filterFilter;
    
    protected $pagerfanta;

        
    /**
     * @inheritDoc
     */
    public function __construct($name, $type = '', TableFactoryInterface $factory, array $options = array())
    {
        if (! isset($options['query'])) {
            $aliasLetter = strtolower(substr($options['repository']->getClassName(), strrpos($options['repository']->getClassName(), '\\') + 1, 1));
            
            $options['query'] = $options['repository']->createQueryBuilder($aliasLetter);
        }
        
        $this->sorterFilter = new QueryBuilderSorter($factory->getContainer());
        $qb = $this->sorterFilter->execute($options['query']);
        
        if (isset($options['filter']))
        {
            $this->filterFilter = $options['filter'];
            $qb = $this->filterFilter->execute($qb);
            unset($options['filter']);
        }
        
        $this->pagerFilter = new QueryBuilderPager($factory->getContainer());
        $this->pagerfanta = $this->pagerFilter->execute($qb);
        
        // override query with pager
        $options['query'] = $this->pagerfanta;
        
        parent::__construct($name, $type, $factory, $options);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getTable($options = array())
    {
        $options['attributes'] = array(
            'class' => 'table table-striped table-condensed table-results'
        );
        
        return parent::getTable($options);
    }
    
    /**
     * @return Tactics\TableBundle\QueryBuilderFilter\QueryBuilderSorter
     */
    public function getSorterFilter()
    {
        return $this->sorterFilter;
    }

    /**
     * @return Tactics\TableBundle\QueryBuilderFilter\QueryBuilderPager
     */
    public function getPagerFilter()
    {
        return $this->pagerFilter;
    }

    /**
     * @return Tactics\TableBundle\QueryBuilderFilter\QueryBuilderFilter
     */
    public function getFilterFilter()
    {
        return $this->filterFilter;
    }
    
    /**
     * @return Pagerfanta\Pagerfanta
     */
    public function getPagerfanta()
    {
        return $this->pagerfanta;
    }    
}

