<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;

/**
 * Displays a hexadecimal value as a color 
 *
 * @author Jeroen Meert <jeroen.meert at tactics.be>
 */
class ColorColumn extends Column
{   
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'color';
    }
}

