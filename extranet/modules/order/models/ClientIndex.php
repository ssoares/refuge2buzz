<?php
/**
*
 * Quote request management. Data import.
 *
 * @category  Extranet_Modules
 * @package   Extranet_Modules_Order

 * @version   $Id: ClientIndex.php 1367 2013-12-27 04:19:31Z ssoares $
 */

/**
 * Database access to the table Members profile for tthe client import.
 *
 * @category  Extranet_Modules
 * @package   Extranet_Modules_Order

 */
class ClientIndex extends Zend_Db_Table
{
    protected $_name = 'MemberProfiles';
}