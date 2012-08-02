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

use Tactics\TableBundle\Table\TableInterface;
use Tactics\TableBundle\Table\ColumnInterface;
use Tactics\TableBundle\Table\ColumnHeaderInterface;

/**
 * @author Gert Vrebos <gert.vrebos at tactics.be>
 */
interface TableFactoryInterface
{
    
    public function createBuilder($name, $type = '', array $options = array());
    
    
    /**
     * Returns a table.
     *
     * @param string                    $name    The name of the table
     * @param string|TableTypeInterface $type    The type of the table
     * @param array                    $options The options
     *
     * @return TableInterface The table 
     *
     * @throws Exception\TableException if any given option is not applicable to the given type
     */
    public function createTable($name, $type = '', array $options = array());

    /**
     * Returns a column.
     *
     * @param string                    $name    The name of the table
     * @param string|TableTypeInterface $type    The type of the table
     * @param ColumnHeader              $columnHeader    
     * @param array                    $options The options
     *
     * @return ColumnInterface The column 
     *
     * @throws Exception\TableException if any given option is not applicable to the given type
     */
    public function createColumn($name, $type = '', ColumnHeaderInterface $columnHeader, array $options = array());

                
    /**
     * Returns a column header.
     *
     * @param string                    $name    The name of the table
     * @param string|TableTypeInterface $type    The type of the table
     * @param array                    $options The options
     *
     * @return ColumnHeaderInterface The table 
     *
     * @throws Exception\TableException if any given option is not applicable to the given type
     */
    public function createColumnHeader($name, $type = '', array $options = array());

}
