<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\Common\Collections\Collection;

class AssociationColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public function getCell($row)
    {
        $cell = parent::getCell($row);

        if ($cell['value'])
        {
            $method = $this->getOption('method');
            $targetMethod = $this->getOption('target_method');
            $objectOrCollection = $row['_object']->$method();

            if ($objectOrCollection instanceof Collection) {
                $names = array();

                foreach ($objectOrCollection as $object) {
                    $names[] = $targetMethod ? $object->$targetMethod() : (string) $object;
                }

                $cell['value'] = implode(', ', $names);
            } elseif ($objectOrCollection && get_class($objectOrCollection) === $this->getOption('target_entity')) {
                $cell['value'] = $targetMethod ? $objectOrCollection->$targetMethod() : (string) $objectOrCollection;
            } else {
                $cell['value'] = null;
            }
        }
        
        return $cell;
    }
    
    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        
        $resolver->setOptional(array('target_method'));
        $resolver->setRequired(array('target_entity'));
    }    
}
