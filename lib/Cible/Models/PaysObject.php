<?php
/**
*
 * Retailer management. Data import.
 *
 * @category  Extranet_Modules
 * @package   Extranet_Modules_Retailer

 * @version   $Id: PaysObject.php 1367 2013-12-27 04:19:31Z ssoares $
 */

/**
 * Manage data for cities
 *
 * @category  Extranet_Modules
 * @package   Extranet_Modules_Retailer

 */
class PaysObject extends DataObject
{
    protected $_dataClass   = 'PaysData';
//    protected $_dataId      = '';
//    protected $_dataColumns = array();

    protected $_indexClass      = 'PaysIndex';
//    protected $_indexId         = '';
    protected $_indexLanguageId = 'CI_LanguageID';
//    protected $_indexColumns    = array();

    protected $_indexSelectColumns = array();
}
