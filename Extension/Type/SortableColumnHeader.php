<?php

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tactics\TableBundle\ColumnHeader;

/**
 * Description of SortableColumnHeader
 *
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class SortableColumnHeader extends ColumnHeader
{
    const ASC     = 'ascending';
    const DESC    = 'descending';
    const NO_SORT = 'not_sorted';

    /**
     * @var $state string
     */
    protected $state = self::NO_SORT;

    /**
     * @var $state string
     */
    protected $route;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'sortable';
    }

    /**
     * Retrieves the column state.
     *
     * @return string The state of the column.
     */
    public function getState()
    {
        return $this->state;    
    }

    /**
     * Sets the column state.
     *
     * @param string The state of the column.
     */
    public function setState($state)
    {
        if (self::ASC !== $state && self::DESC !== $state && self::NO_SORT !== $state) {
            throw new \Exception('Unknown state '.$state.'. Possible values are '.
                self::ASC.', '.self::DESC.' or '. self::NO_SORT.'.'
            );
        }

        $this->state = $state;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }
}
