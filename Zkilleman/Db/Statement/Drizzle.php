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
    
    public function closeCursor()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function columnCount()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function errorCode()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function errorInfo()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function nextRowset()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
    
    public function rowCount()
    {
        //TODO: Implement
        require_once 'Zkilleman/Db/Adapter/Drizzle/Exception.php';
        throw new Zkilleman_Db_Adapter_Drizzle_Exception(
                __FUNCTION__.'() is not implemented');
    }
}