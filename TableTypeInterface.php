<?php

namespace Tactics\TableBundle;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of TableTypeInterface
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
interface TableTypeInterface
{
    /**
     * Builds the form with the given builder
     * 
     * @param TableBuilderInterface
     */
    public function build(TableBuilderInterface $builder, array $options);


    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver);
    
    /**
     * Returns the type of table builder to be used.
     * 
     * @return string The type of table builder
     */
    public function getBuilderType();
}

