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

    public function save($id, $data, $langId)
    {
        $addrId = 0;
        $oGP = new GenericProfilesObject();
        $genData = $oGP->findData(array($oGP->getDataId() => $id));
        if ($genData[0]['GP_Language'] != $langId)
            $langId = $genData[0]['GP_Language'];

        if (isset($data[$this->_addrDataField]))
        {
            $oAdress = new AddressObject();
            $addrBill = $data[$this->_addrDataField];
            if (!empty($data[$this->_addrShipDataField]))
                $addrShip = $data[$this->_addrShipDataField];
        }

        if (!empty($addrBill))
        {
            $addrId = $oAdress->save($data[$this->_addrField], $addrBill, $langId);

            if (isset($addrShip['duplicate']) && $addrShip['duplicate'] == 1)
            {
                $addrBill['A_Duplicate'] = $billId;
                $shipId = $oAdress->save($addrShip[$this->_addrShipField], $addrBill, $langId);
            }
            elseif (!empty($data[$this->_addrShipDataField]))
            {
                $addrShip['A_Duplicate'] = 0;
                $shipId = $oAdress->save($addrShip[$this->_addrShipField], $addrShip, $langId);
            }
            $data[$this->_addrField] = $addrId;
        }

        if (!empty($data['MP_Password']))
            $data['MP_Password'] = md5($data['MP_Password']);
        else
            unset($data['MP_Password']);

        parent::save($id, $data, $langId);
    }

    public function findData($filters = array(), $useQry = false)
    {
        $billAddr = array();
        $shipAddr = array();
        $oAddress = new AddressObject();
        $oGeneric = new GenericProfilesObject();
        if (isset($filters[$this->_foreignKey]))
        {
            $genericData = $oGeneric->populate($filters[$this->_foreignKey], 1);
            $langId   = $genericData['GP_Language'];
        }
        else
            $langId   = Zend_Registry::get('languageID');

        $data = parent::findData($filters);

        if (!empty($data))
        {
            $data = array_merge($genericData, $data[0]);
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
        $memberId = $memberData['member_id'];
        $addrId = $memberData['addrBill'];

        $oAddres = new AddressObject();
        $oTaxes = new TaxesObject();

        $stateId = $oAddres->getStateId($addrId);
        $taxRate = $oTaxes->getTaxData($stateId);

        $memberData['taxProv'] = $taxRate['TP_Rate'];
        $memberData['taxCode'] = $taxRate['TZ_GroupName'];

        return $memberData;
    }

}