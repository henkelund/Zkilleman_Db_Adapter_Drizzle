<?php
/**
 * Zkilleman Drizzle Zend_Db_Adapter
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Zkilleman
 * @package    Zkilleman_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2011 Henrik Hedelund <henke.hedelund@improove.se>
 * @license    New BSD License
 */


/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';


/**
 * @category   Zkilleman
 * @package    Zkilleman_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2011 Henrik Hedelund <henke.hedelund@gmail.com>
 * @license    New BSD License
 */
class Zkilleman_Db_Adapter_Drizzle extends Zend_Db_Adapter_Abstract
{
    
    /**
     * Creates a connection to the database.
     *
     * @return void
     * @throws Zkilleman_Db_Adapter_Drizzle_Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        
        if (!extension_loaded('drizzle')) {
            
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    'The Drizzle extension is required for this adapter but ' .
                    'the extension is not loaded');
        }
    }
    
    public function listTables()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    public function describeTable($tableName, $schemaName = null)
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    public function isConnected()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    public function closeConnection()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    public function prepare($sql)
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    protected function _beginTransaction()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    protected function _commit()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    protected function _rollBack()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    /**
     * Set the fetch mode.
     *
     * @param int $mode
     * @return void
     * @throws Zkilleman_Db_Adapter_Drizzle_Exception
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case Zend_Db::FETCH_LAZY:
            case Zend_Db::FETCH_ASSOC:
            case Zend_Db::FETCH_NUM:
            case Zend_Db::FETCH_BOTH:
            case Zend_Db::FETCH_NAMED:
            case Zend_Db::FETCH_OBJ:
                $this->_fetchMode = $mode;
                break;
            case Zend_Db::FETCH_BOUND: 
                
                require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
                throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                        'FETCH_BOUND is not supported yet');
                break;
            default:
                
                require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
                throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                        sprintf('Invalid fetch mode "%s" specified', $mode));
        }
    }

    public function limit($sql, $count, $offset = 0)
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    public function supportsParameters($type)
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    public function getServerVersion()
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
}
