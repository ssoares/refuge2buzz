<?php
/**
* 
 * Orders management.
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order

 * @version   $Id: OrderLinesData.php 422 2011-03-24 03:25:10Z ssoares $
 */

/**
 * Database access to the table "Orders_Lines"
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order

 */
class OrderLinesData extends Zend_Db_Table
{
    protected $_name = 'Orders_Lines';
}