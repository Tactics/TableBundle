<?php

namespace Tactics\TableBundle\ObjectTable;

class ObjectTable
{
    protected $modelCriteria;
    protected $objectPeer;
    protected $reflector;
    protected $columns = array();

    /**
     * Constructor.
     */
    public function __construct(\ModelCriteria $mc, array $columns = array())
    {
        $this->modelCriteria = $mc;

        $peerName = $this->modelCriteria->getModelPeerName();

        $this->objectPeer = new $peerName();
        $this->reflector  = new \ReflectionClass($this->modelCriteria->getModelName());

        // Create columns from fieldnames and user specified columns.
        foreach ($this->getFieldnames() as $key => $fieldName) {
            // Default settings.
            $displayname = str_replace('_', ' ', ucfirst(strtolower($fieldName)));
            $method      = $this->translateRawColnameToMethod($fieldName);
            $visible     = true;
            $order       = $key;

            // Override settings if user specified this column.
            foreach ($columns as $key => $array) {
                if (array_key_exists('colname', $array) && $array['colname'] == $fieldName) {
                    if (array_key_exists('displayname', $array)) $displayname = $array['displayname'];
                    // Check if user specified method exists.
                    if (array_key_exists('method', $array)) {
                        if (! $this->reflector->hasMethod($array['method'])) {
                            throw new Exception('Method '.$array['method'].
                                ' does not exist for class '.$this->reflector->getName() 
                            );
                        }

                        $method = $array['method'];
                    }
                    if (array_key_exists('visible', $array)) $visible = (boolean) $array['visible'];
                    $order = $key;
                }
                
                // Unset all columns that appear in database so that only 
                // custom columns remain in the $columns array.
                unset($columns[$key]);
            }

            $columnArr = array(
                'colname'     => $fieldName,
                'displayname' => $displayname,
                'visible'     => $visible,
                'method'      => $method
            );

            if (array_key_exists($order, $this->columns)) {
                $slice = array_slice($order, $this->columns);
                array_splice($order, $this->columns);
                $this->columns[$order] = $columnArr;
                array_splice($slice, count($slice), 0, $this->columns);
            } else {
                $this->columns[$order] = $columnArr;
            }
        }
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
     * Render to html.
     *
     * @return string
     */
    public function render()
    {
        // Headers
        $html = '<table></thead><tr>';

        foreach ($this->columns as $column) {
          if (! $column['visible']) continue;

          $html .= '<th>'.$column['displayname'].'</th>';
        }

        // Body
        $html .= '</tr></thead><tbody>';

        foreach ($this->modelCriteria->find() as $object)
        {
            $html . '<tr>';

            foreach ($this->columns as $column) {
                $html .= '<td>';

                if ($val = $object->$column['method']()) {
                    // The only thing propel returns that can't be cast to string 
                    // is a DateTime object.
                    // @todo Is this true?
                    // @todo This is configurable, how do I handle this?
                    if (is_object($val) && get_class($val) === 'DateTime')
                    {
                        $html .= $val->format('d/m/Y');
                    } elseif (is_object($val)) {
                        throw new Exception('ObjectTable can\'t handle '.get_class($val));
                    } else {
                        $html .= $val;
                    }
                } else {
                    $html .= '&nbsp;';
                }

                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
