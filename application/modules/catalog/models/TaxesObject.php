<?php
/**
 * Module Utilities
 * Management of the references data.
 *
 * @category  Application_Module
 * @package   Application_Module_Utilities
 *

 * @license   Empty
 * @version   $Id: TaxesObject.php 435 2011-03-28 03:57:25Z ssoares $id
 */

/**
 * Manage data from references table.
 *
 * @category  Application_Module
 * @package   Application_Module_References
 *

 * @license   Empty
 * @version   $Id: TaxesObject.php 435 2011-03-28 03:57:25Z ssoares $id
 */
class TaxesObject extends DataObject
{
    protected $_dataClass   = 'TaxesData';

    protected $_indexClass      = '';
    protected $_indexLanguageId = '';

    /**
     * Return the taxe value of the selected state.
     *
     * @param int $stateId
     *
     * @return float
     */
    public function getTaxData($stateId)
    {
        $value = 0;

        $select = $this->_db->select()
            ->from($this->_oDataTableName, array('TP_Rate', 'TP_Code'))
            ->join('Catalog_TaxeZone',
                    'TZ_ID = TP_StateId',
                    array('TZ_GroupName')
                    )
            ->where('TP_StateId = ?', $stateId);

        $value = $this->_db->fetchRow($select);
        
        return $value;
    }
}