<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tactics\TableBundle\Column;

/**
 * Displays an array
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class ArrayColumn extends Column
{
    /**
     * Sets the default options
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver->setRequired(array('separator'));
        $resolver->setDefaults(array('separator' => ', '));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'array';
    }
}

