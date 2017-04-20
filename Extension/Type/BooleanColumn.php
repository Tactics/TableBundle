<?php
namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class BooleanColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public function getCell($row)
    {
        $cell = parent::getCell($row);
        $cell['value'] = (boolean) $cell['value'] ? 'Yes' : 'No';
        
        return $cell;
    }
}
