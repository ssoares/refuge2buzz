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
    protected $_indexClass      = '';
    protected $_constraint      = '';
    protected $_foreignKey      = '';
    protected $_searchColumns = array(
        'data' => array(
            'GP_LastName',
            'GP_FirstName',
            'GP_Email')
        );
    protected $_keywords = array();
    protected $_filters = array();
    protected $_profileId = 0;
    protected $_profileType = 0;
    protected $_delField = 'GP_Deleted';
    protected $_forceExact = false;
    protected $_nbDayLimit = 0;
    public function getNbDayLimit(){
        return $this->_nbDayLimit;
    }

    public function setNbDayLimit($nbDayLimit){
        $this->_nbDayLimit = $nbDayLimit;
        return $this;
    }

    public function getDelField()
    {
        return $this->_delField;
    }

    public function setDelField($delField)
    {
        $this->_delField = $delField;
        return $this;
    }

    public function getProfileType(){
        return $this->_profileType;
    }

    public function setProfileType($profileType){
        $this->_profileType = $profileType;
        return $this;
    }

    public function getProfileId()
    {
        return $this->_profileId;
    }

    public function setProfileId($profileId)
    {
        $this->_profileId = (int)$profileId;
        return $this;
    }

    public function getKeywords(){
        return $this->_keywords;
    }

    public function setKeywords($keywords)
    {
//        if (is_array($keywords)){
//            $this->_keywords = array_merge($this->_keywords, $keywords);
//        }else{
//            array_push($this->_keywords, $keywords);
//        }
        $this->_keywords =  $keywords;
        return $this;
    }

    public function setFilters($keywords = array())
    {
        foreach($keywords as $value)
        {
            foreach($this->_searchColumns['data'] as $key){
                $this->_filters[$key] = $value;
            }
        }
    }

    public function autocompleteSearch()
    {
        $result = array();
        $this->_query = parent::getAll(null, false);
        $this->setWhereClause(array('data' => $this->_keywords));
        $data = parent::findData(array(), true);
        foreach($data as $key => $value){
            $result[] = array('id' => $value[$this->_dataId],
                'label' => $value['GP_FirstName'] . ' ' . $value['GP_LastName']
                . ' (' . $value['GP_Email'] . ')',
                'value' => $value['GP_FirstName'] . ' ' . $value['GP_LastName']
                );
}

        return $result;
    }

    public function saveProfile($data, $langId)
    {
        $filters = array('GP_Email' => $data['GP_Email']);
        $profile = $this->findData($filters);
        if (!empty($profile)){
            $this->_profileId = (int)$profile[0][$this->_dataId];
            if (empty($data[$this->_dataId])){
                $data[$this->_dataId] = $this->_profileId;
            }
            $this->save($this->_profileId, $data, $langId);
        }else{
            $this->_profileId = $this->insert($data, $langId);
        }

        return $this->_profileId;
    }

    public function insert($data, $langId)
    {
        $data = $this->_formatInputData($data);
        unset($data[$this->_dataId]);
        $data['GP_CreaDate'] = date('Y-m-d H:i:s');
        return parent::insert($data, $langId);
    }

    public function save($id, $data, $langId)
    {
        $data = $this->_formatInputData($data);
        parent::save($id, $data, $langId);
    }

    protected function _formatInputData(array $data)
    {
        $data = parent::_formatInputData($data);
        foreach ($data as $field => $values)
        {
            switch ($field)
            {
                case 'GP_Password':
                    if (empty($values)){
                        unset($data[$field]);
                    }else{
                        $data[$field] = md5($values);
                    }
                    break;
                default:
                    break;
            }
        }
        return $data;
    }

    public function delete($id)
    {
        parent::delete($id);
//        $this->save($id, array($this->_delField => 1), 1);
    }


    /**
     * Test if profile already exists for form validation and defined if name
     * is the same.
     *
     * @param array $data form data
     * @return boolean
     */
    public function validateExistingAccount($data)
    {
        $existsButDifferent = false;
        $filters = array('GP_Email' => $data['GP_Email']);
        $this->_forceExact = true;
        $profile = $this->findData($filters);
        if (!empty($profile)){
            $lname = $profile[0]['GP_LastName'];
            $fname = $profile[0]['GP_FirstName'];
            if ($lname != $data['GP_LastName']
                || $fname != $data['GP_FirstName']){
                $existsButDifferent = true;
            }
        }

        return $existsButDifferent;
    }

    public function findProfilesData()
    {
        if (empty($this->_profileId) || empty($this->_profileType)){
            throw new Zend_Exception('No profile id or no type of profile defined');
        }
        $data = array();
        switch($this->_profileType)
        {
            default: // 22
//                $oDonor = new DonorsObject();
//                $data = $oDonor->populate($this->_profileId, 1);
                break;
        }
        if (!empty($data)){
            $data = array_merge($data, $this->populate($this->_profileId, 1));
        }

        return $data;
    }

    public function getAll($langId = null, $array = true, $id = null)
    {
        parent::getAll($langId, false, $id);
        $this->_query->where($this->_delField . ' = ?', 0);

        if ($array){
            $typeData = $this->_oData->fetchAll($this->_query)->toArray();
        }else{
            $typeData = $this->_query;
        }

        return $typeData;
    }

}
