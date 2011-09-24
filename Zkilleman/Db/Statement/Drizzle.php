<?php
/**
 * Zkilleman Drizzle Zend_Db_Statement
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Zkilleman
 * @package    Zkilleman_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2011 Henrik Hedelund <henke.hedelund@gmail.com>
 * @license    New BSD License
 */


/**
 * @see Zend_Db_Statement
 */
require_once 'Zend/Db/Statement.php';


/**
 * @category   Zkilleman
 * @package    Zkilleman_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2011 Henrik Hedelund <henke.hedelund@gmail.com>
 * @license    New BSD License
 */
class Zkilleman_Db_Statement_Drizzle extends Zend_Db_Statement
{
    
    /**
     * 
     * @var string
     */
    protected $_insertId;
    
    /**
     * @param  string $sql
     * @return void
     * @throws Zkilleman_Db_Statement_Drizzle_Exception
     */
    public function _prepare($sql)
    {
        $drizzleCon = $this->_adapter->getConnection();
        $this->_stmt = @$drizzleCon->query($sql);
        
        if ($this->_stmt === false || $drizzleCon->errorCode()) {
            require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
            throw new Zkilleman_Db_Statement_Drizzle_Exception(
                    sprintf('Drizzle prepare error: %s', $drizzleCon->error()),
                    $drizzleCon->errorCode());
        }
    }
    
    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zkilleman_Db_Statement_Drizzle_Exception
     */
    protected function _execute(array $params = null)
    {
        if (!$this->_stmt) {
            return false;
        }
        
        $this->_insertId = $this->_stmt->insertId();
        
        return $this->_stmt->errorCode() == 0;
    }
    
    /**
     * Insert ID of this statement or '0'
     * 
     * @return int
     */
    public function insertId()
    {
        return (int) $this->_insertId;
    }
    
    public function closeCursor()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
        throw new Zkilleman_Db_Statement_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function columnCount()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
        throw new Zkilleman_Db_Statement_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function errorCode()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
        throw new Zkilleman_Db_Statement_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function errorInfo()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
        throw new Zkilleman_Db_Statement_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
        throw new Zkilleman_Db_Statement_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zkilleman_Db_Statement_Drizzle_Exception
     */
    public function nextRowset()
    {
        require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
        throw new Zkilleman_Db_Statement_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     */
    public function rowCount()
    {
        return $this->_stmt ? $this->_stmt->affectedRows() : 0;
    }
}