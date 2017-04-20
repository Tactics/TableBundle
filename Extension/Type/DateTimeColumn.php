<?php

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tactics\TableBundle\Column;

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

        $resolver->setDefined(array('show_date', 'show_time'));
        $resolver->setDefaults(array('show_date' => true, 'show_time' => true));
    }
}
