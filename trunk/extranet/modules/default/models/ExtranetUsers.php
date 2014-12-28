<?php
class ExtranetUsers extends Zend_Db_Table
{
    protected $_name = 'Extranet_Users';
    protected $_id = 0;

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function getAdminEqualOrOver($adminGroupID, $getData = false)
    {
        $select = $this->select();
        $select->from('Extranet_Users',array("EU_ID","EU_LName","EU_FName",'EU_Email'))
                ->joinLeft('Extranet_UsersGroups','EU_ID=EUG_UserID AND EUG_GroupID >= '. $adminGroupID,array(""))
//                ->where("EUG_GroupID >= ?", $adminGroupID)
                ->group("EU_Email");

        if ($getData){
            $data = $this->fetchAll($select)->toArray();
        }else{
            $data = $select;
        }

        return $data;
    }

    public function populate()
    {
        $select = $this->select();
        if (!empty($this->_id)){
            $select->where("EU_ID = ?", $this->_id);
        }

        return $this->fetchRow($select);
    }

}