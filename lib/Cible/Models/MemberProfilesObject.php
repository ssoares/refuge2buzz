<?php
/**
 * Member Profile data
 * Management of the Items.
 *
 * @category  Cible
 * @package   Cible_MemberProfilesObject
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: MemberProfilesObject.php 1372 2013-12-27 22:07:54Z ssoares $id
 */

/**
 * Manages Member Profile data.
 *
 * @category  Cible
 * @package   Cible_MemberProfiles
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: MemberProfilesObject.php 1372 2013-12-27 22:07:54Z ssoares $id
 */
class MemberProfilesObject extends DataObject
{

    protected $_dataClass   = 'MemberProfilesData';

    protected $_foreignKey      = 'MP_GenericProfileId';
    protected $_addrField       = 'MP_AddressId';
    protected $_addrShipField   = 'MP_ShippingAddrId';
    protected $_addrDataField   = 'address';
    protected $_addrShipDataField  = 'addressShipping';
    protected $_oGeneric  = null;
    protected $_profileId  = 0;

    public function getProfileId()
    {
        return $this->_profileId;
    }

    public function setProfileId($profileId)
    {
        $this->_profileId = (int)$profileId;
        return $this;
    }

    public function getOGeneric(){
        return $this->_oGeneric;
    }
    public function setOGeneric($oGeneric = '')
    {
        if (!empty($oGeneric)){
            $this->_oGeneric = new $oGeneric();
        }else{
            $this->_oGeneric = new GenericProfilesObject();
        }
        return $this;
    }
    public function addProfile($data, $langId = 0)
    {
        if (!$langId && !empty($data['languageId'])){
            $langId = $data['languageId'];
        }elseif(!$langId){
            $langId = Zend_Registry::get('languageID');
        }
        $data = $this->_setAddressData($data, $langId);
        $this->_profileId = $this->_oGeneric->insert($data, $langId);
        $data[$this->_dataId] = $this->_profileId;
        $this->insert($data, $langId);
        return $this->_profileId;
    }
    public function save($id, $data, $langId)
    {
        $addrId = 0;
        $this->setOGeneric();
        $genData = $this->_oGeneric->findData(array($this->_oGeneric->getDataId() => $id));
        if ($genData[0]['GP_Language'] != $langId){
            $langId = $genData[0]['GP_Language'];
        }
        $this->_oGeneric->save($id, $data['identification'], $langId);
        $data = $this->_setAddressData($data, $langId, $data[$this->_addrField]);
        if (!empty($data['MP_Password'])){
            $data['MP_Password'] = md5($data['MP_Password']);
        }else{
            unset($data['MP_Password']);
        }
        parent::save($id, $data, $langId);
    }

    public function findData($filters = array(), $useQry = false)
    {
        $billAddr = array();
        $shipAddr = array();
        $oAddress = new AddressObject();
        $oGeneric = new GenericProfilesObject();
        if ($this->_profileId > 0 || isset($filters[$this->_foreignKey])){
            if (!empty($filters[$this->_foreignKey])){
                $this->_profileId = $filters[$this->_foreignKey];
            }
            $genericData = $oGeneric->populate($this->_profileId, 1);
            $langId = $genericData['GP_Language'];
        }
        else{
            $tmp = $oGeneric->findData($filters);
            $genericData = $tmp[0];
            $langId = Zend_Registry::get('languageID');
        }

        $data = parent::findData($filters);

        if (!empty($data))
        {
            $data = $data[0];
            $data['identification'] = $genericData;
            $data['currentLanguage'] = $langId;
            $addrId = $data[$this->_addrField];
            if (!empty($data[$this->_addrShipField]))
                $shipId = $data[$this->_addrShipField];

            if (!empty($shipId))
            {
                $shipAddr = $oAddress->getAll($langId, true, $shipId);
                $shipAddr = $shipAddr[0];
                $shipAddr[$this->_addrShipField] = $shipId;
            }

            if (!empty($addrId))
            {
                $billAddr = $oAddress->getAll($langId, true, $addrId);
                $billAddr = $billAddr[0];
                $billAddr[$this->_addrField] = $addrId;
            }

            if (isset($shipAddr['A_Duplicate']) && !$shipAddr['A_Duplicate'])
                $shipAddr['duplicate'] = 0;

            $data[$this->_addrDataField] = $billAddr;
            $data[$this->_addrShipDataField] = $shipAddr;
        }

        return $data;
    }
    public function getDateList()
    {
        $select = parent::getAll(null, false);
        $cols = array('MP_DateCreate');
        $select->distinct(true);

        $data = $this->_db->fetchAll($select);
        foreach ($data as $values)
        {
            $date = substr($values['MP_DateCreate'], 0, 10);
            $dates[$date] = $date;
        }

        $dates = array_unique($dates);

        return $dates;
    }

    /**
     * Allows to add values of taxes for orders to the customer data.
     *
     * @param array $memberData
     *
     * @return array
     */
    public function addTaxRate(array $memberData)
    {
        $data = array();
        $addrId = $memberData[$this->_addrField];

        $oAddres = new AddressObject();
        $oTaxes = new TaxesObject();

        $stateId = !empty($addrId)?$oAddres->getStateId($addrId):$memberData[$this->_addrDataField]['A_StateId'];
        $taxRate = $oTaxes->getTaxData($stateId);

        $memberData['taxProv'] = $taxRate['TP_Rate'];
        $memberData['taxCode'] = $taxRate['TZ_GroupName'];

        return $memberData;
    }

//    protected function _setAddressData($data, $langId, $id = null)
//    {
//        if (isset($data[$this->_addrDataField])){
//            $oAdress = new AddressObject();
//            $firstAddr = $data[$this->_addrDataField];
//            if (!empty($data[$this->_addrShipDataField])){
//                $secAddr = $data[$this->_addrShipDataField];
//            }
//        }
//        if (!empty($firstAddr)){
//            $addrId = $oAdress->save(null, $firstAddr, $langId);
//            if (isset($secAddr['duplicate']) && $secAddr['duplicate'] == 1){
//                $firstAddr['A_Duplicate'] = $billId;
//                $shipId = $oAdress->save($secAddr[$this->_addrShipField], $firstAddr, $langId);
//            }elseif (!empty($data[$this->_addrShipDataField])){
//                $secAddr['A_Duplicate'] = 0;
//                $shipId = $oAdress->save($secAddr[$this->_addrShipField], $secAddr, $langId);
//            }
//            $data[$this->_addrField] = $addrId;
//        }
//
//        return $data;
//    }

}