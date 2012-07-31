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
    public function __construct($mc, array $columns = array())
    {
        $this->modelCriteria = $mc;

        $peerName = $this->modelCriteria->getModelPeerName();

        $this->objectPeer = new $peerName();
        $this->reflector  = new \ReflectionClass($this->modelCriteria->getModelName());

        // Create columns from fieldnames and user specified columns.
        foreach ($this->getFieldnames() as $key => $fieldName) {
            // Default settings.
            $columnArr = array(
                'displayname' => str_replace('_', ' ', ucfirst(strtolower($fieldName))),
                'method'      => $this->translateRawColnameToMethod($fieldName),
                'visible'     => false,
                'order'       => $key
            );

            // Override settings if user specified this column.
            foreach ($columns as $key => $array) {
                if (array_key_exists('colname', $array) && $array['colname'] == $fieldName) {
                    if (array_key_exists('displayname', $array)) $columnArr['displayname'] = $array['displayname'];
                    // Check if user specified method exists.
                    if (array_key_exists('method', $array) && $this->hasMethod($array['method'])) {
                        $columnArr['method'] = $array['method'];
                    }
                    if (array_key_exists('visible', $array)) $columnArr['visible'] = (boolean) $array['visible'];
                    if (array_key_exists('order', $array)) $columnArr['order'] = $array['order']; 

                    // Keep only the custom column (columns that don't appear 
                    // in db) in the array. Loop & add these later.
                    unset($columns[$key]); 
                } 
            }

            $this->addColumn($columnArr);
        }
        
        // Add custom columns.
        foreach ($columns as $columnArr) {
            if (! array_key_exists('displayname', $columnArr) || ! array_key_exists('method', $columnArr)) {
                throw new Exception('Column with insufficient parameters');
            }

            if (! array_key_exists('visible', $columnArr)) {
              $columnArr['visible'] = false;
              }

              if ($this->hasMethod($columnArr['method'])) {
                $this->addColumn($columnArr);
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
       * Remove a column.
       *
       * @param $rawColName  The raw columnname
       * @param $displayName The displayname
       */
      public function removeColumn($rawColname = null, $displayName = null)
      {
          if (null === $rawColname && null === $displayName) {
              throw new Exception('No RAW_COLNAME or displayname specified.');
          }
          
          $name = $rawColname ? 'raw_colname' : 'displayname';
          $val  = $rawColname ? $rawColname   : $displayName;

          foreach ($this->columns as $key => $columnArr) {
              if (array_key_exists($name, $columnArr) && $columnArr[$name] === $val) {
                  unset($this->columns[$key]); 
              }
          }
      }

      /**
       * @param  boolean $visible Only return visible columns if true.
       * @return array
       */
      public function getColumns($visible = true)
      {
          // Only show visible columns
          if ($visible) {
              $columns = array();

              foreach ($this->columns as $column)
              {
                  if (! $column['visible']) continue;

                  $columns[] = $column;
              }

              return $columns;
          } else {
              return $this->columns();
          }
      }

      /**
       * Render to html.
     *
     * @return string
     */
    public function render()
    {
        // Headers
        $html = '<table class="table"><thead><tr>';
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
              if ($column['visible'] == true) {
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

          }
              $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
