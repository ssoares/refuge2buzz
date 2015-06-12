<?php
/**
*
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order

 * @version   $Id: RequestedItemObject.php 1367 2013-12-27 04:19:31Z ssoares $
 */

/**
 * Manage data in database for the products.
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order

 */
class RequestedItemObject extends DataObject
{
    protected $_dataClass   = 'RequestedItemData';
//    protected $_dataId      = '';
//    protected $_dataColumns = array();

    protected $_indexClass      = 'RequestedItem';
    protected $_indexId         = '';
    protected $_indexLanguageId = '';
    protected $_indexColumns    = array();

    protected $_query;

}