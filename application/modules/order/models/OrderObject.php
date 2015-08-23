<?php
/**
 * Cible Solutions -
 * Orders management.
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order
 * @copyright Copyright (c) Cibles solutions d'affaires. (http://www.ciblesolutions.com)
 * @version   $Id: OrderObject.php 422 2011-03-24 03:25:10Z ssoares $
 */

/**
 * Manage data in database for the orderss.
 *
 * @category  Application_Modules
 * @package   Application_Modules_Order
 * @copyright Copyright (c) Cibles solutions d'affaires. (http://www.ciblesolutions.com)
 */
class OrderObject extends DataObject
{
    protected $_dataClass   = 'OrderData';
    protected $_query;
    protected $_profileId = 0;
    protected $_foreignKey      = 'GP_MemberID';
    protected $_passwordField   = 'GP_Password';
    protected $_addrField       = 'MP_AddressId';
    protected $_delField        = '';
    protected $_addrShipField   = 'MP_ShippingAddrId';
    protected $_addrDataField   = 'address';
    protected $_addrShipDataField  = 'addressShipping';
    protected $_oGeneric  = null;

    public function getOGeneric(){
        return $this->_oGeneric;
    }
    public function setOGeneric($oGeneric = '')
    {
        if (!empty($oGeneric)){
            $this->_oGeneric = new $oGeneric();
        }else{
            $this->_oGeneric = new MemberProfilesObject();
        }
        return $this;
    }

    public function getModificationTitle()
    {
        return Cible_Translation::getClientText('account_modify_page_title');
    }

    /**
     * Fetch orders data to generate csv file.
     *
     * @param string $orderColumns The list of colums to set in the select statement.
     * @param string $status       The order status to export only available orders.
     * @param int    $id           Order id, allows to fetch data for an other part of the file.
     *
     * @return array
     */
    public function getDataForExport($orderColumns = '*', $status = 'aucun', $id = null)
    {
        $select = $this->_db->select()
                ->from($this->_oDataTableName,
                        $orderColumns)
                ->where('O_Status = ?', $status);

        if ($id)
            $select->where('O_ID = ?', $id);

        $data = $this->_db->fetchAll($select);

        return $data;
    }

    public function addProfile($data, $langId = 0)
    {
        $this->_oGeneric->setOGeneric();
        if (!$langId && !empty($data['identification']['GP_LanguageId'])){
            $langId = $data['identification']['GP_LanguageId'];
        }elseif(!$langId){
            $langId = Cible_Controller_Action::getDefaultEditLanguage();
        }
        if (isset($data['identification'])){
            $tmp = $data['identification'];
            unset($data['identification']);
            $data = array_merge($data, $tmp);
        }
        $id = $this->_oGeneric->addProfile($data, $langId);
//        $this->_oGeneric->save($id, $data, $langId);

        return $id;
    }
}