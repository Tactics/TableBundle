<?php

namespace Tactics\TableBundle;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tactics\TableBundle\ColumnInterface;

/**
 * Description of AbstractColumnTypeExtension
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class AbstractColumnTypeExtension implements ColumnTypeExtensionInterface {
    
    public function execute(ColumnInterface $column, array &$row, array &$cell) {
        
    }

    /**
     * Overrides the default options from the extended type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'text';
    }
}

