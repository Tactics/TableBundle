<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tactics\TableBundle;

use Tactics\TableBundle\Exception\TableException;
use Tactics\TableBundle\Exception\UnknownTypeException;
use Tactics\TableBundle\ColumnHeaderInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of TableFactory
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class TableFactory implements TableFactoryInterface, ContainerAwareInterface
{
    /**
     * @var $container ContainerInterface A ContainerInterface instance.
     */
    protected $container;
    
    /**
     * @var $columnExtensions array A list of columnExtensions
     */
    protected $columnExtensions;
    
    /**
     * @var $headerExtensions array A list of headerExtensions
     */
    protected $headerExtensions;
    

    /**
     * Constructor
     *
     * @param ContainerInterface $container A ContainerInterface instance.
     * @param array $columnExtensions A list of columnExtensions
     * @param array $headerExtensions A list of headerExtensions
     */
    public function __construct(ContainerInterface $container, array $columnExtensions = array(), $headerExtensions = array())
    {
        $this->setContainer($container);
        
        $this->columnExtensions = array();
        foreach($columnExtensions as $id)
        {
            $this->columnExtensions[] = $container->get($id);
        }
        
        $this->headerExtensions = array();
        foreach($headerExtensions as $id)
        {
            $this->headerExtensions[] = $container->get($id);
        }
        
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

   /**
    * {@inheritdoc
    */ 
    public function createBuilder($type = '', array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        $options = $resolver->resolve($options);

        $name = $options['table_class'];
        unset($options['table_class']);
        
        
        if ($type instanceof TableTypeInterface)
        {
            $builderType = $type->getBuilderType();
        }
        else
        {
            $builderType = $type;
        }

        // todo resolving via dependency injection container
        $tableBuilderClass = "Tactics\\TableBundle\\Extension\\Builder\\" . \Symfony\Component\DependencyInjection\Container::camelize($builderType) . 'TableBuilder';
        
        if (! class_exists($tableBuilderClass))
        {
            throw new UnknownTypeException("TableBuilder type '" . $builderType . "' could not be resolved. (Guess was: $tableBuilderClass )");
        }
        
        // todo table type as option.. somehow
        $builder = new $tableBuilderClass($name, '', $this, $options);
        
        if ($type instanceof TableTypeInterface)
        {
            $type->build($builder, $options);
        }
        
        return $builder;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function createTable($name, $type = '', array $options = array())
    {
        // todo resolving via dependency injection container
        
        $tableClass = "Tactics\\TableBundle\\Extension\\Table\\" . \Symfony\Component\DependencyInjection\Container::camelize($type) . 'Table';
        
        if (! class_exists($tableClass))
        {
            throw new UnknownTypeException("Table type '" . $type . "' could not be resolved. (Guess was: $tableClass )");
        }
        
        return new $tableClass($name, $options);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createColumn($name, $type = '', ColumnHeaderInterface $columnHeader, array $options = array())
    {
        // todo resolving via dependency injection container
        
        $columnClass = "Tactics\\TableBundle\\Extension\\Type\\" . \Symfony\Component\DependencyInjection\Container::camelize($type) . 'Column';
        
        if (! class_exists($columnClass))
        {
            throw new UnknownTypeException("Column type '" . $type . "' could not be resolved. (Guess was: $columnClass )");
        }

        return new $columnClass($name, $columnHeader, $options, $this->getColumnExtensionsForType($type));
    }

    
    /**
     * {@inheritdoc}
     */
    public function createColumnHeader($name, $type = '', array $options = array())
    {
      $columnHeaderClass = "Tactics\\TableBundle\\Extension\\Type\\" . \Symfony\Component\DependencyInjection\Container::camelize($type) . 'ColumnHeader';
        
        if (! class_exists($columnHeaderClass))
        {
            throw new UnknownTypeException("ColumnHeader type '" . $type . "' could not be resolved. (Guess was: $columnHeaderClass )");
        }
        
        $type = new $columnHeaderClass($name, $options);

        return $type;
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('model_criteria', 'query', 'repository', 'table_class', 'filter', 'namespace', 'default_sort'));

        $resolver->setOptional(array('header_type', 'column_type'));

        $resolver
            ->setDefaults(array(
                'table_class' => 'table',
        ));

        return $resolver;
    }
    
    /**
     * Returns column extensions for the given type
     * 
     * @param string $type
     * 
     * @return array[]ColumnTypeExtensionInterface
     */
    protected function getColumnExtensionsForType($type)
    {
        // todo: extensions per type; take into account type inheritance?  
        
        return $this->columnExtensions;
    }
    
    /**
     * Returns header extensions for the given type
     * 
     * @param string $type
     * 
     * @return array[]HeaderTypeExtensionInterface
     */
    protected function getHeaderExtensionsForType($type)
    {
        // todo: extensions per type; take into account type inheritance?  
        
        return $this->headerExtensions;
    }
    
}

