<?php

namespace Tactics\TableBundle\Extension\Builder;

use Tactics\TableBundle\TableBuilder;
use Tactics\TableBundle\TableFactoryInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Description of PropelTableBuilder
 *
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
class PropelTableBuilder extends TableBuilder
{
    protected $modelCriteria;
    protected $objectPeer;
    protected $reflector;
    protected $columns = array();

    /**
     * @inheritDoc
     */    
    public function __construct($name, $type = '', TableFactoryInterface $factory, array $options = array())
    {
        parent::__construct($name, $type, $factory, $options);
        
        $this->modelCriteria = $this->options['model_criteria'];
        
        $peerName = $this->modelCriteria->getModelPeerName();

        $this->objectPeer = new $peerName();
        $this->reflector  = new \ReflectionClass($this->modelCriteria->getModelName());
        
    }
    
    
    /**
     * 
     */ 
    public function addAll(array $exclude = array())
    {
        foreach (array_diff($this->getFieldnames(), $exclude) as $key => $fieldName)
        {
            $this->add($fieldName);
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */     
    public function create($name, $type = null, array $options = array())
    {
        // guess type based on modelcriteria properties
        if (null === $type)
        {
            // gue
        }

        return parent::create($name, $type, $options);
    }

    /**
     * Proxy for modelPeer::getFieldnames.
     *
     * @return array
     */
    private function getFieldnames()
    {
        return $this->objectPeer->getFieldNames(\BasePeer::TYPE_RAW_COLNAME);
    }

    /**
     * Translate raw colname to method.
     *
     * @param $rawColname string 
     * @return string
     */
    private function translateRawColnameToMethod($rawColname)
    {
        if (array_search($rawColname, $this->getFieldnames()) === false) {
            throw new Exception('Unknown column'.$rawColname);
        }

        return 'get'.$this->objectPeer->translateFieldName(
            $rawColname, 
            \BasePeer::TYPE_RAW_COLNAME,
            \BasePeer::TYPE_PHPNAME
        );
    }

    /**
     * Proxy for ReflectorClass::hasMethod
     *
     * @throws Exception When method is not found.
     * @return true When method is found.
     */
    private function hasMethod($method)
    {
        if (! $this->reflector->hasMethod($method)) {
            throw new Exception('Method '.$method.
                ' does not exist for class '.$this->reflector->getName() 
            );
        }

        return true;
    }

    /**
     * Add a column.
     * 
     * @param array
     */
    public function addColumn(array $columnArr)
    {
        if (array_key_exists('order', $columnArr) && array_key_exists($columnArr['order'], $this->columns)) {
            $slice = array_slice($this->columns, $columnArr['order']);
            array_splice($this->columns, $columnArr['order']);
            $this->columns[$columnArr['order']] = $columnArr;
            array_splice($slice, count($slice), 0, $this->columns);
        } else {
            $this->columns[] = $columnArr;
        }
    }

          
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        
        $resolver->setRequired(array('model_criteria'));
    }

}

