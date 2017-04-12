<?php

namespace Tactics\TableBundle;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tactics\TableBundle\ColumnInterface;

/**
 * Description of TableTypeExtensionInterface
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */

interface ColumnTypeExtensionInterface
{
    public function execute(ColumnInterface $column, array &$row, array &$cell);

    /**
     * Overrides the default options from the extended type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType();
}

