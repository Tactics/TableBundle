<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Muylaert <aaron.muylaert at tactics.be>
 */
class DateTimeColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'datetime';
    }    

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setOptional(array('show_date', 'show_time'));
        $resolver->setDefaults(array('show_date' => true, 'show_time' => true));
    }
}
