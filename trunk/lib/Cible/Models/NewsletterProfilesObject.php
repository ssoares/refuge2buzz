<?php
/**
 * Newsletter Profile data
 * Management of the Items.
 *
 * @category  Cible
 * @package   Cible_NewsletterProfilesObject
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: NewsletterProfilesObject.php 1633 2014-07-04 17:18:24Z ssoares $id
 */

/**
 * Manages Newsletter Profile data.
 *
 * @category  Cible
 * @package   Cible_NewsletterProfiles
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: NewsletterProfilesObject.php 1633 2014-07-04 17:18:24Z ssoares $id
 */
class NewsletterProfilesObject extends DataObject
{

    protected $_dataClass   = 'NewsletterProfilesData';
//    protected $_dataId      = '';
//    protected $_dataColumns = array();

    protected $_indexClass      = '';
//    protected $_indexId         = '';
    protected $_indexLanguageId = '';
//    protected $_indexColumns    = array();
    protected $_constraint      = '';
    protected $_foreignKey      = 'NP_GenericProfileMemberID';

    public function findData($filters = array())
    {
        $data = parent::findData($filters);
        if (!empty($data))
        {
            $data = $data[0];
            $data['NP_Categories'] = explode(',', $data['NP_Categories']);
        }
        return $data;
    }

    public function save($id, $data, $langId)
    {
        if (!isset($data['NP_Categories']))
            $data['NP_Categories'] = 0;

        parent::save($id, $data, $langId);
    }
}
