<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            } elseif ($objectOrCollection) {
                $cell['value'] = $targetMethod ? $objectOrCollection->$targetMethod() : (string) $objectOrCollection;

                $cell = $this->generateUrl($objectOrCollection, $cell);
            } else {
                $cell['value'] = null;
            }
        }
        
        return $cell;
    }
    
    /**
     * Sets the default options for this table.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver->setOptional(array('target_method', 'entity_route_resolver'));
    }    

    /**
     * @return Tactics\Bundle\EntityRouteBundle\EntityRouteResolver|null
     */
    private function getEntityRouteResolver()
    {
        return $this->getOption('entity_route_resolver'); 
    }

    private function generateUrl($entity, $cell)
    {
        $resolver = $this->getEntityRouteResolver();

        if (
            ! isset($cell['route']) &&
            $resolver && 
            $resolver->hasEntityRoute($entity)
        ) {
            $cell['url'] = $resolver->generateUrl($entity);
        }

        return $cell;
    }
}
