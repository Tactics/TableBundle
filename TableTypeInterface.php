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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName();
    
    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);

}

