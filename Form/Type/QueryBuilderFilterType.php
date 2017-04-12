<?php

namespace Tactics\TableBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class QueryBuilderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getBlockPrefix()
    {
        return 'filter_by';
    }
}
