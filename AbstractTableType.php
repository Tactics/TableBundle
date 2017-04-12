<?php

namespace Tactics\TableBundle;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of AbstractTableType
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
abstract class AbstractTableType implements TableTypeInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        
    }
}

