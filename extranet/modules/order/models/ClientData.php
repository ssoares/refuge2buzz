<?php
/**
*
 * Quote request management. Data import.
 *
 * @category  Extranet_Modules
 * @package   Extranet_Modules_Order

 * @version   $Id: ClientData.php 1367 2013-12-27 04:19:31Z ssoares $
 */

/**
 * Database access to the table GenericProfiles for clients import.
 *
 * @category  Extranet_Modules
 * @package   Extranet_Modules_Order

 */
class ClientData extends Zend_Db_Table
{
    protected $_name = 'GenericProfiles';
}