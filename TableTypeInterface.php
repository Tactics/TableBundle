<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tactics\TableBundle;

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
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);
    
    /**
     * Returns the type of table builder to be used.
     * 
     * @return string The type of table builder
     */
    public function getBuilderType();
}

