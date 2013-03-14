<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        
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

