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
     * @var int
     */
    protected $_insertId = 0;
    
    /**
     * 
     * @var array
     */
    protected $_columns = null;
    
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
        
        if (!$this->_stmt->buffer()) {
            require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
            throw new Zkilleman_Db_Statement_Drizzle_Exception(
                    sprintf('Drizzle excecute error: %s', $drizzleCon->error()),
                    $drizzleCon->errorCode());
        }
        
        $this->_columns = array();
        while (($column = $this->_stmt->columnNext()) != null) {
            $this->_columns[] = $column->name();
        }
        
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
    
    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor()
    {
        $this->_stmt->rowSeek(0);
        return true;
    }
    
    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement hasn't been executed.
     *
     * @return int The number of columns.
     */
    public function columnCount()
    {
        if (is_array($this->_columns)) {
            return count($this->_columns);
        }
        return null;
    }
    
    /**
     * Retrieves the error code, if any, associated with the last operation on
     * the statement handle.
     *
     * @return string error code.
     */
    public function errorCode()
    {
        if (!$this->_stmt) {
            return false;
        }
        return substr($this->_stmt->sqlstate(), 0, 5);
    }

    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        if (!$this->_stmt) {
            return false;
        }
        return array(
            substr($this->_stmt->sqlstate(), 0, 5),
            $this->_stmt->errorCode(),
            $this->_stmt->error(),
        );
    }
    
    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->_stmt) {
            return false;
        }
        
        $row = $this->_stmt->rowNext();
        
        // eof or err
        if (!$row) {
            return false;
        }
        
        if ($style === null) {
            $style = $this->_fetchMode;
        }
        
        switch ($style) {
            case Zend_Db::FETCH_NUM:
                // default returned by rowNext()
                break;
            case Zend_Db::FETCH_ASSOC:
                $row = array_combine($this->_columns, $row);
                break;
            case Zend_Db::FETCH_BOTH:
                $assoc = array_combine($this->_columns, $row);
                $row = array_merge($row, $assoc);
                break;
            case Zend_Db::FETCH_OBJ:
                $row = (object) array_combine($this->_columns, $row);
                break;
            case Zend_Db::FETCH_BOUND:
                $assoc = array_combine($this->_columns, $row);
                $row = array_merge($row, $assoc);
                return $this->_fetchBound($row);
                break;
            default:
                
                require_once 'Zkilleman/Db/Statement/Drizzle/Exception.php';
                throw new Zkilleman_Db_Statement_Drizzle_Exception(
                        sprintf('Invalid fetch mode "%s" specified', $style));
                break;
        }
        return $row;
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