<?php

namespace Tactics\TableBundle\Extension\Type;

use Tactics\TableBundle\ColumnTypeExtensionInterface;
use Tactics\TableBundle\AbstractColumnTypeExtension;
use Tactics\TableBundle\ColumnInterface;
use Tactics\TableBundle\Exception\InvalidOptionException;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of FunctionColumnExtension
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class FunctionColumnExtension extends AbstractColumnTypeExtension implements ContainerAwareInterface {
    
    private $container;
    
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * Constructor
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute(ColumnInterface $column, array &$row, array &$cell)
    {
        $function = $column->getOption('function');
        
        if ($function && is_callable($function))
        {
            $function($column, $row, $cell, $this->container);
        }
    }

    /**
     * Overrides the default options from the extended type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('function'));
    }
}

