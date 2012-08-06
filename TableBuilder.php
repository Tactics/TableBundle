<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license intableation, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tactics\TableBundle;

use Tactics\TableBundle\Table;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Description of TableBuilderInterface
 *
 * @author gert
 */
class TableBuilder implements \IteratorAggregate, TableBuilderInterface
{
    /**
     * The table factory.
     *
     * @var TableFactoryInterface
     */
    protected $factory;
    
    
    /**
     * The type of table this builder creates
     * 
     * @var string 
     */
    protected $type;
    
    /**
     * The name of the table this builder creates
     * 
     * @var string 
     */
    protected $name;
    
    /**
     * List of columns in the TableBuilder
     * 
     * @var array
     */
    protected $columns = array();
  
    /**
     * @var array The options for the table builder
     */
    protected $options = array();
 
  
    /**
     * Creates a new table builder.
     *
     * @param string                   $name
     * @param TableFactoryInterface     $factory
     * @param array                    $options
     */
    public function __construct($name, $type = '', TableFactoryInterface $factory, array $options = array())
    {
        $this->factory = $factory;
        $this->type = $type;
        $this->name = $name;
        
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        
        $this->options = $resolver->resolve($options);
    }
    
    /**
     * {@inheritdoc}
     */
    public function add($name, $type = null, array $options = array())
    {
        $this->columns[$name] = array(
            'type'    => $type,
            'options' => $options,
        );

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function create($name, $type = null, $headerType = null, array $options = array())
    {
        // todo replace quick hack to clean up options, this is dirty.
        $columnOptions = isset($options['column']) ? $options['column'] : array();
        $headerOptions = isset($options['header']) ? $options['header'] : array();

        if (null === $type) {
            $type = $this->options['default_column_type'];
        }

        if (null === $headerType) {
            $headerType = $this->options['default_column_header_type'];
        }

        if (null !== $type) {
            $headerName = isset($options['header']['value']) ? $options['header']['value'] : $name;

            $header = $this->factory->createColumnHeader($headerName, $headerType, $headerOptions);
            
            return $this->factory->createColumn($name, $type, $header, $columnOptions);
        }

        //return $this->factory->createBuilderForProperty($name, null, $options, $this);
    }    
    
    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->create($this->columns[$name]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        unset($this->columns[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * todo: de-rocketscience this please, headache.
     * todo: seriously :p
     */
    public function all()
    {
        $columns = array();
        
        foreach($this->columns as $name => $config)
        {
            $headerType = (isset($config['options']['header']) && isset($config['options']['header']['type'])) ?
                $config['options']['header']['type'] : null;

            $columns[$name] = $this->create($name, $config['type'], $headerType, $config['options']);
        }
        
        return $columns;
    }

    /**
     * Returns the number of columns
     */
    public function count()
    {
        return count($this->columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $columns = $this->all();
        
        return new \ArrayIterator($columns);
    }    
    
    /**
     * {@inheritdoc}
     */
    public function getTableFactory()
    {
        return $this->factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        // todo: create!
        $table = $this->factory->createTable($this->name, $this->type, array());
        
        foreach ($this as $column)
        {
            $table->add($column);
        }
        
        return $table;
    }
    
    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'default_column_type' => 'text',
                'default_column_header_type' => 'text'
        ));
    }
    
  
}

