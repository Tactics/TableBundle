<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\Column;
use Tactics\TableBundle\ColumnHeader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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

          // convert to string ( call __toString() )
          $cell['value'] = sprintf('%s', $object);
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
        
        $resolver->setRequired(array('foreign_table'));
    }    
}
