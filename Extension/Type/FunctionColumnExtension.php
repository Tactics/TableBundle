<?php

namespace Tactics\TableBundle\Extension\Type;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tactics\TableBundle\AbstractColumnTypeExtension;
use Tactics\TableBundle\ColumnInterface;

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
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('function');
    }
}

