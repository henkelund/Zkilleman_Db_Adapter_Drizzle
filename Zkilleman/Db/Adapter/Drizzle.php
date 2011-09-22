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
     *
     * @var drizzle 
     */
    protected static $_drizzle = null;
    
    /**
     * Constructor.
     * 
     * @param  array|Zend_Config $config 
     * @throws Zend_Db_Adapter_Exception
     * @throws Zkilleman_Db_Adapter_Drizzle_Exception
     */
    public function __construct($config)
    {
        if (self::$_drizzle === null) {
            if (!extension_loaded('drizzle')) {
            
                require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
                throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                        'The Drizzle extension is required for this adapter ' .
                        'but the extension is not loaded');
            } else {
                self::$_drizzle = new drizzle();
            }
        }
        parent::__construct($config); 
    }
    
    /**
     * Creates a connection to the database.
     *
     * @return void
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        
        $host = isset($this->_config['host']) ? 
                (string) $this->_config['host'] : 'localhost';
        
        $port = isset($this->_config['port']) ? 
                (int) $this->_config['port'] : 4427;
        
        // suppress warnings and throw exception instead
        $this->_connection = @self::$_drizzle->addTcp(
            $host, 
            $port, 
            $this->_config['username'], 
            $this->_config['password'], 
            $this->_config['dbname'], 
            DRIZZLE_CON_NONE // no options
        );
        
        if (!$this->isConnected() || self::$_drizzle->errorCode()) {
            
            $this->closeConnection();
            
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    self::$_drizzle->error());
        }
    }
    
    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $result = array();
        
        $queryResult = @$this->getConnection()->query('SHOW TABLES');
        if ($queryResult && $queryResult->buffer()) {
            while ($row = $queryResult->rowNext()) {
                $result[] = $row[0];
            }
            drizzle_result_free($queryResult);
        } else {
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    $this->getConnection()->error());
        }
        return $result;
    }
    
    public function describeTable($tableName, $schemaName = null)
    {
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return (bool) ($this->_connection instanceof DrizzleCon);
    }
    
    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        if ($this->isConnected()) {
            $this->_connection->close();
        }
        $this->_connection = null;
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
