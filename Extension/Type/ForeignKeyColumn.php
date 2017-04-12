<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class ForeignKeyColumn extends Column
{
    
    /**
     * {@inheritdoc}
     */
    public function getCell($row)
    {
        $cell = parent::getCell($row);

        if ($cell['value'])
        {
            $peerClassName = $this->getOption('foreign_table')->getPeerClassname();
            $object = $peerClassName::retrieveByPK($cell['value']);

            if ($this->getOption('foreign_method')) {
                $cell['value'] = call_user_func(array($object, $this->getOption('foreign_method')));
            }
            else {
                // convert to string ( call __toString() )
                $cell['value'] = sprintf('%s', $object);
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
        
        $resolver->setRequired(array('foreign_table'));
        $resolver->setOptional(array('foreign_method'));
    }    
}
