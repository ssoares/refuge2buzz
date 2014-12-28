<?php
/**
 * Generic Profile data
 * Management of the Items.
 *
 * @category  Cible
 * @package   Cible_GenericProfilesObject
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: GenericProfilesObject.php 730 2011-12-09 03:45:25Z ssoares $id
 */

/**
 * Manages Generic Profile data.
 *
 * @category  Cible
 * @package   Cible_GenericProfiles
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: GenericProfilesObject.php 730 2011-12-09 03:45:25Z ssoares $id
 */
class GenericProfilesObject extends DataObject
{

    protected $_dataClass   = 'GenericProfilesData';
//    protected $_dataId      = '';
//    protected $_dataColumns = array();

    protected $_indexClass      = '';
//    protected $_indexId         = '';
    protected $_indexLanguageId = '';
//    protected $_indexColumns    = array();
    protected $_constraint      = '';
    protected $_foreignKey      = '';

}