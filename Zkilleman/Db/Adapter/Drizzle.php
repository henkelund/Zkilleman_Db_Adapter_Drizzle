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
 * @copyright  Copyright (c) 2011 Henrik Hedelund <henke.hedelund@gmail.com>
 * @license    New BSD License
 */


/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Db_Profiler
 */
require_once 'Zend/Db/Profiler.php';

/**
 * @see Zend_Db_Select
 */
require_once 'Zend/Db/Select.php';

/**
 * @see Zkilleman_Db_Statement_Drizzle
 */
require_once 'Zkilleman/Db/Statement/Drizzle.php';


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
     * Drizzle only supports the UTF8 character set so a char is always 4 bytes
     */
    const DRIZZLE_CHAR_SIZE = 4;

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE, or Zend_Db::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = array(
        Zend_Db::INT_TYPE    => Zend_Db::INT_TYPE,
        Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
        Zend_Db::FLOAT_TYPE  => Zend_Db::FLOAT_TYPE,
        'INT'                => Zend_Db::INT_TYPE,
        'INTEGER'            => Zend_Db::INT_TYPE,
        'MEDIUMINT'          => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'TINYINT'            => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'SERIAL'             => Zend_Db::BIGINT_TYPE,
        'DEC'                => Zend_Db::FLOAT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'DOUBLE'             => Zend_Db::FLOAT_TYPE,
        'DOUBLE PRECISION'   => Zend_Db::FLOAT_TYPE,
        'FIXED'              => Zend_Db::FLOAT_TYPE,
        'FLOAT'              => Zend_Db::FLOAT_TYPE
    );
    
    /**
     * Keys are Drizzle datatype constants and values are SQL datatype names
     * 
     * @var array Drizzle datatype constant - name mapping
     */
    protected $_drizzleDataTypes = array(
        DRIZZLE_COLUMN_TYPE_BIT         => 'BIT',
        DRIZZLE_COLUMN_TYPE_BLOB        => 'BLOB',
        DRIZZLE_COLUMN_TYPE_DATE        => 'DATE',
        DRIZZLE_COLUMN_TYPE_DATETIME    => 'DATETIME',
        DRIZZLE_COLUMN_TYPE_DECIMAL     => 'DECIMAL',
        DRIZZLE_COLUMN_TYPE_DOUBLE      => 'DOUBLE',
        DRIZZLE_COLUMN_TYPE_ENUM        => 'ENUM',
        DRIZZLE_COLUMN_TYPE_FLOAT       => 'FLOAT',
        DRIZZLE_COLUMN_TYPE_GEOMETRY    => 'GEOMETRY',
        DRIZZLE_COLUMN_TYPE_INT24       => 'INT24',
        DRIZZLE_COLUMN_TYPE_LONG        => 'LONG',
        DRIZZLE_COLUMN_TYPE_LONG_BLOB   => 'LONG_BLOB',
        DRIZZLE_COLUMN_TYPE_LONGLONG    => 'LONGLONG',
        DRIZZLE_COLUMN_TYPE_MEDIUM_BLOB => 'MEDIUM_BLOB',
        DRIZZLE_COLUMN_TYPE_NEWDATE     => 'NEWDATE',
        DRIZZLE_COLUMN_TYPE_NEWDECIMAL  => 'NEWDECIMAL',
        DRIZZLE_COLUMN_TYPE_NULL        => 'NULL',
        DRIZZLE_COLUMN_TYPE_SET         => 'SET',
        DRIZZLE_COLUMN_TYPE_SHORT       => 'SHORT',
        DRIZZLE_COLUMN_TYPE_STRING      => 'STRING',
        DRIZZLE_COLUMN_TYPE_TIME        => 'TIME',
        DRIZZLE_COLUMN_TYPE_TIMESTAMP   => 'TIMESTAMP',
        DRIZZLE_COLUMN_TYPE_TINY        => 'TINY',
        DRIZZLE_COLUMN_TYPE_TINY_BLOB   => 'TINY_BLOB',
        DRIZZLE_COLUMN_TYPE_VARCHAR     => 'VARCHAR',
        DRIZZLE_COLUMN_TYPE_VAR_STRING  => 'VAR_STRING',
        DRIZZLE_COLUMN_TYPE_VIRTUAL     => 'VIRTUAL',
        DRIZZLE_COLUMN_TYPE_YEAR        => 'YEAR'
    );
    
    /**
     * A subset of $_drizzleDataTypes containing only text types
     * 
     * @var array Drizzle datatype constant - name mapping
     */
    protected $_drizzleTextDataTypes = array(
        DRIZZLE_COLUMN_TYPE_BLOB,
        DRIZZLE_COLUMN_TYPE_LONG_BLOB,
        DRIZZLE_COLUMN_TYPE_MEDIUM_BLOB,
        DRIZZLE_COLUMN_TYPE_TINY_BLOB,
        DRIZZLE_COLUMN_TYPE_VARCHAR,
        DRIZZLE_COLUMN_TYPE_VAR_STRING
    );
    
    /**
     *
     * @var drizzle 
     */
    protected static $_drizzle = null;
    
    /**
     * 
     * @var string
     */
    protected static $_lastInsertId = 0;
    
    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Zkilleman_Db_Statement_Drizzle';
    
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
                self::$_drizzle = new Drizzle();
            }
        }
        parent::__construct($config); 
    }
    
    /**
     * Quote a raw string.
     *
     * @param mixed $value Raw string
     *
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return "'" . self::$_drizzle->escapeString($value) . "'";
    }
    
    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed  $sql  The SQL statement with placeholders.
     *                      May be a string or Zend_Db_Select.
     * @param  mixed  $bind An array of data to bind to the placeholders.
     * @return Zend_Db_Statement_Interface
     */
    public function query($sql, $bind = array())
    {
        $stmt = parent::query($sql, $bind);
        if ($stmt instanceof Zkilleman_Db_Statement_Drizzle) {
            if (($insertId = $stmt->insertId()) > 0) {
                self::$_lastInsertId = $insertId;
            }
        }
        return $stmt;
    }
    
    /**
     * Creates a connection to the database.
     *
     * @return void
     */
    protected function _connect()
    {
        if ($this->isConnected()) {
            return;
        }
        
        $host = isset($this->_config['host']) ? 
                (string) $this->_config['host'] : 'localhost';
        
        $port = isset($this->_config['port']) ? 
                (int) $this->_config['port'] : DRIZZLE_DEFAULT_TCP_PORT;
        
        // suppress warnings and throw exception instead
        $this->_connection = @self::$_drizzle->addTcp(
            $host, 
            $port, 
            $this->_config['username'], 
            $this->_config['password'], 
            $this->_config['dbname'], 
            DRIZZLE_CON_NONE // no options
        );
        
        @$this->_connection->connect();
        
        if (!$this->isConnected() || self::$_drizzle->errorCode()) {
            
            $this->closeConnection();
            
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    self::$_drizzle->error());
        }
    }
    
    /**
     * Returns the symbol the adapter uses for delimiting identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '`';
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
        } else {
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    $this->_connection->error());
        }
        return $result;
    }
    
    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        $result = array();
        
        $drizzleCon = $this->getConnection();
        
        $schemaName = $schemaName ? 
                $schemaName : $drizzleCon->db();
        
        $drizzleResult = @$drizzleCon->query(
            $this->limit(
                'SELECT * FROM ' . $this->quoteIdentifier("$schemaName.$tableName"), 
                0 // no rows, just columns
            )
        );
        
        if (!$drizzleResult->buffer()) {
            return $result; // silently fail
        }
        
        $i = 0; // column index
        $p = 0; // primary index
        
        while (($column = $drizzleResult->columnNext()) != null) {
            
            $flags = $column->flags();
            $primary = (bool) ($flags & DRIZZLE_COLUMN_FLAGS_PRI_KEY);
            $size = $column->size();

            $length = null;
            if ($size > 0 && 
                    in_array($column->type(), $this->_drizzleTextDataTypes)) {
                // if text type and size > 0, divide length by char size
                $length = $size/self::DRIZZLE_CHAR_SIZE;
            }

            $precision = $size;
            $scale = (int) $column->decimals();
            if ($scale <= 0) {
                // if no decimals, interpret scale & precision as not applicable
                $precision = null;
                $scale = null;
            }

            $result[$column->name()] = array(
                'SCHEMA_NAME'      => $column->db(),
                'TABLE_NAME'       => $column->table(),
                'COLUMN_NAME'      => $column->name(),
                'COLUMN_POSITION'  => ++$i,
                'DATA_TYPE'        => $this->_drizzleDataTypes[$column->type()],
                'DEFAULT'          => $column->defaultValue(),
                'NULLABLE'         => 
                    (bool) (~$flags & DRIZZLE_COLUMN_FLAGS_NOT_NULL),
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => 
                    (bool) ($flags & DRIZZLE_COLUMN_FLAGS_UNSIGNED),
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primary ? ++$p : null,
                'IDENTITY'         => 
                    (bool) ($flags & DRIZZLE_COLUMN_FLAGS_AUTO_INCREMENT)
            );
        }
        
        return $result;
    }
    
    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return (bool) ($this->_connection instanceof DrizzleCon) &&
                $this->_connection->status() !== DRIZZLE_CON_STATUS_NONE;
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
    
    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param  string  $sql  SQL query
     * @return Zkilleman_Db_Statement_Drizzle
     */
    public function prepare($sql)
    {
        $this->_connect();
        $stmtClass = $this->_defaultStmtClass;
        if (!class_exists($stmtClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($stmtClass);
        }
        $stmt = new $stmtClass($this, $sql);
        if ($stmt === false) {
            return false;
        }
        $stmt->setFetchMode($this->_fetchMode);
        return $stmt;
    }
    
    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * Drizzle does not support sequences, so $tableName and $primaryKey are ignored.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return int
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        return self::$_lastInsertId;
    }
    
    /**
     * Begin a transaction.
     *
     * @return void
     * @throws Zkilleman_Db_Adapter_Drizzle_Exception
     */
    protected function _beginTransaction()
    {
        if (!$this->_supportsTransactions()) {
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    'The current Drizzle setup doesn\'t support transactions');
        } elseif (!$this->_inTransaction()) {
            $this->_connection->query('START TRANSACTION');
        } // else { already in transaction }
    }

    /**
     * Commit a transaction.
     *
     * @return void
     * @throws Zkilleman_Db_Adapter_Drizzle_Exception
     */
    protected function _commit()
    {
        if (!$this->_supportsTransactions()) {
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    'The current Drizzle setup doesn\'t support transactions');
        } elseif ($this->_inTransaction()) {
            $this->_connection->query('COMMIT');
        } // else { already in autocommit mode }
    }

    /**
     * Roll-back a transaction.
     *
     * @return void
     * @throws Zkilleman_Db_Adapter_Drizzle_Exception
     */
    protected function _rollBack()
    {
        if (!$this->_supportsTransactions()) {
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    'The current Drizzle setup doesn\'t support transactions');
        } elseif ($this->_inTransaction()) {
            $this->_connection->query('ROLLBACK');
        } // else { can't roll back if not in a transaction }
    }
    
    /**
     * Check wheter transactions are supported 
     * 
     * @return bool
     */
    protected function _supportsTransactions()
    {
        return (bool) ($this->getConnection()->capabilities() & 
                DRIZZLE_CAPABILITIES_TRANSACTIONS);
    }
    
    /**
     * Check if a transaction is started
     * 
     * @return bool
     */
    protected function _inTransaction()
    {
        return (bool) ($this->getConnection()->status() 
                & DRIZZLE_CON_STATUS_IN_TRANS);
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

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param int $count
     * @param int $offset OPTIONAL
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = (int) $count;
        if ($count < 0) {
            
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    sprintf('LIMIT argument count = %s is not valid', $count));
        }

        $offset = (int) $offset;
        if ($offset < 0) {
            
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    sprintf('LIMIT argument offset = %s is not valid', $count));
        }

        $sql .= ' LIMIT ' . $count;
        if ($offset > 0) {
            $sql .= ' OFFSET ' . $offset;
        }

        return $sql;
    }

    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     * @return bool
     */
    public function supportsParameters($type)
    {
        return false;
    }

    /**
     * Retrieve server version in PHP style
     *
     *@return string
     */
    public function getServerVersion()
    {
        return $this->getConnection()->serverVersion();
    }
}
