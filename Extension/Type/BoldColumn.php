<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Table\Column;

/**
 * Description of TextColumn
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class BoldColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'bold';
    }

}

