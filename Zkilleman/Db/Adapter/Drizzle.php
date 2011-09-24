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
        
        $this->_connection->connect();
        
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
                    $this->getConnection()->error());
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
        $schemaName = $schemaName ? 
                $schemaName : $this->getConnection()->db();
        
        // columns table
        $ct = $this->quoteIdentifier('INFORMATION_SCHEMA.COLUMNS');
        $cta = $this->quoteIdentifier('isc'); // alias
        // keys table
        $kt = $this->quoteIdentifier('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
        $kta = $this->quoteIdentifier('iskcu'); // alias
        // columns table schema name column
        $ctsc = $this->quoteIdentifier('TABLE_SCHEMA');
        // keys table schema name column
        $ktsc = $ctsc;
        // columns table table name column
        $cttc = $this->quoteIdentifier('TABLE_NAME');
        // keys table table name column
        $kttc = $cttc;
        // columns table column name column
        $ctcc = $this->quoteIdentifier('COLUMN_NAME');
        // keys table column name column
        $ktcc = $ctcc;
        // keys table constraint name column
        $ktconc = $this->quoteIdentifier('CONSTRAINT_NAME');
        $ktconca = $this->quoteIdentifier('PRIMARY'); // alias
        // keys table ordinal position column
        $ktopc = $this->quoteIdentifier('ORDINAL_POSITION');
        $ktopca = $this->quoteIdentifier('PRIMARY_POSITION'); // alias
        
        $sql = 
            "SELECT $cta.*, $kta.$ktconc AS $ktconca, $kta.$ktopc AS $ktopca 
                FROM $ct AS $cta
                    LEFT JOIN $kt AS $kta
                        ON ($cta.$ctcc=$kta.$ktcc 
                            AND $cta.$ctsc=$kta.$ktsc 
                            AND $cta.$cttc=$kta.$kttc)
                WHERE ($cta.$ctsc='$schemaName' AND $cta.$cttc='$tableName')";
        
        var_dump($sql);
        $result = array();
        
        $queryResult = @$this->getConnection()->query($sql);
        if ($queryResult && $queryResult->buffer()) {
            while ($row = $queryResult->rowNext()) {
                $result[$row[3]] = array(
                    'SCHEMA_NAME'      => $row[1],
                    'TABLE_NAME'       => $row[2],
                    'COLUMN_NAME'      => $row[3],
                    'COLUMN_POSITION'  => $row[4],
                    'DATA_TYPE'        => $row[7],
                    'DEFAULT'          => $row[5],
                    'NULLABLE'         => (bool) intval($row[6]),
                    'LENGTH'           => $row[8],
                    'SCALE'            => $row[12],
                    'PRECISION'        => $row[10],
                    'UNSIGNED'         => null, //TODO
                    'PRIMARY'          => $row[23] == 'PRIMARY',
                    'PRIMARY_POSITION' => $row[24],
                    'IDENTITY'         => null //TODO
                );
            }
        } else {
            
            require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
            throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                    $this->getConnection()->error());
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
    
    public function prepare($sql)
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }
    
    protected function _beginTransaction()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    protected function _commit()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    protected function _rollBack()
    {
        //TODO: Implement
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
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception('Not implemented yet');
    }

    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     * @return bool
     */
    public function supportsParameters($type)
    {
        switch ($type) {
            case 'positional':
                return true;
            case 'named':
            default:
                return false;
        }
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
