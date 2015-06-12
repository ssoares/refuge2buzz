<?php

/**
 * LICENSE
 *
 * @category
 * @package
 * @license   Empty
 */

/**
 * Description of NewsletterModelsObject
 *
 * @category
 * @package
 * @license   Empty
 * @version   $Id: NewsletterModelsObject.php 1018 2012-08-10 20:16:23Z freynolds $
 */
class NewsletterModelsObject extends DataObject
{
    protected $_dataClass = 'NewsletterModels';
    protected $_indexClass = 'NewsletterModelsIndex';
    protected $_indexLanguageId = 'NMI_LanguageID';

    public function getDefault()
    {
        $data = array();
        $select = parent::getAll(null, false);
        $select->where('NM_IsDefault=?', 1);


        $data = $this->_db->fetchAll($select);

        return $data;
    }
}