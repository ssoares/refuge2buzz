<?php
/**
*
 * Order management.
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order

 * @version   $Id: ItemIndex.php 435 2011-03-28 03:57:25Z ssoares $
 */

/**
 * Database access to the table "Catalog_ItemsIndex"
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order

 */
class ItemIndex extends Zend_Db_Table
{
    protected $_name = 'Catalog_ItemsIndex';
}