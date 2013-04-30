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
    public function add($name, $type = null, $headerTypeOrOptions = null, array $options = array())
    {
        $headerType = is_string($headerTypeOrOptions) ? $headerTypeOrOptions : null;
        $options = is_array($headerTypeOrOptions) ? $headerTypeOrOptions : $options;
      
        $this->columns[$name] = array(
            'type'        => $type,
            'header_type' => $headerType,
            'options'     => $options,
        );

        return $this;
    }

    /**
     * Extracts options from array with a specific namespace prefix and returns
     * an array with only the options from the matching namespace, namespace removed
     * 
     * eg  array('column/type' => 'one') becomes array('type' => 'one')
     * 
     * @param array $options
     * @param string $ns
     * @return array with options in namespace, namespace in key removed
     */
    protected function extractNamespacedOptions($options, $ns)
    {
        $extracted = array();

        foreach ($options as $key => $value) {
            if (strpos($key, $ns) !== false) {
                $arr = explode('/', $key);
                $extracted[end($arr)] = $value;
            }
        }

        return $extracted;
    }

    /**
     * {@inheritdoc}
     */
    public function create($name, $type = null, $headerType = null, array $options = array())
    {
        // todo replace quick hack to clean up options, this is dirty.
        $columnOptions = $this->extractNamespacedOptions($options, 'column');
        $headerOptions = $this->extractNamespacedOptions($options, 'header');

        // Set default types if none specified.
        if (null === $type) {
            $type = $this->options['column_type'];
        }

        if (null === $headerType) {
            $headerType = $this->options['header_type'];
        }

        if ($type == 'actions') {
            foreach ($columnOptions['actions'] as $action => $options) {
                if (
                    isset($options['required_roles']) &&
                    $options['required_roles']
                ) {
                    $security = $this->factory->getContainer()->get('security.context');

                    $disabled = true;

                    foreach ($options['required_roles'] as $role) {
                        if ($security->isGranted($role)) {
                            $disabled = false;
                            break;
                        } 
                    }

                    $columnOptions['actions'][$action]['disabled'] = $disabled ? : (isset($options['disabled']) ? $options['disabled'] : $disabled);
                }
            }
        }


        if (null !== $type) {
            $headerName = isset($headerOptions['value']) ? $headerOptions['value'] : ucfirst($name);

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
            $headerType = (isset($config['header_type'])) ?
                $config['header_type'] : null;

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
    public function getTable($options = array())
    {
        $table = $this->factory->createTable($this->name, $this->type, $options);
        
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
                'column_type' => 'text',
                'header_type' => 'text'
        ));
    }
}

