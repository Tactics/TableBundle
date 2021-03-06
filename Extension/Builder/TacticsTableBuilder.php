<?php

namespace Tactics\TableBundle\Extension\Builder;

use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderPager;
use Tactics\TableBundle\QueryBuilderFilter\QueryBuilderSorter;
use Tactics\TableBundle\TableFactoryInterface;

/**
 * Description of TacticsDoctrineTableBuilder
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class TacticsTableBuilder extends DoctrineTableBuilder
{

    /**
     * @var $sorterFilter Tactics\Bundle\TableBundle\QueryBuilderFilter\QueryBuilderSorter
     */
    protected $sorterFilter;

    /**
     * @var $pagerFilter Tactics\Bundle\TableBundle\QueryBuilderFilter\QueryBuilderPager
     */
    protected $pagerFilter;

    /**
     * @var $filterFilter Tactics\Bundle\TableBundle\QueryBuilderFilter\QueryBuilderFilter
     */
    protected $filterFilter;
    
    /**
     * @var $pagerfanta Pagerfanta\Pagerfanta
     */
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
        
        $sorterNamespace = null;
        $filterNamespace = null;
        $pagerNamespace = null;

        if (isset($options['namespace'])) {
            $sorterNamespace = 'sorter/'.$options['namespace'];
            $filterNamespace = 'filter/'.$options['namespace'];
            $pagerNamespace = 'pager/'.$options['namespace'];

            $this->setSorterNamespace($sorterNamespace);
            $this->setFilterNamespace($filterNamespace);
            $this->setPagerNamespace($pagerNamespace);
            // @todo fix uglyness using OptionsResolver.
            unset($options['namespace']);
        }

        if (isset($options['default_sort']) && $options['default_sort']) {
            $sorterOptions = array('default_sort' => $options['default_sort']);
            unset($options['default_sort']);
        } else {
            $sorterOptions = array();
        }

        $this->sorterFilter = new QueryBuilderSorter($factory->getContainer());
        $qb = $this->sorterFilter->execute($options['query'], $sorterNamespace, $sorterOptions);
        
        if (isset($options['filter']))
        {
            $this->filterFilter = $options['filter'];
            $qb = $this->filterFilter->execute($qb, $filterNamespace);
            unset($options['filter']);
        }
        
        $this->pagerFilter = new QueryBuilderPager($factory->getContainer());
        $this->pagerfanta = $this->pagerFilter->execute($qb, $pagerNamespace);
        
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

