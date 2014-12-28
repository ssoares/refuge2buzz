<?php

    class FonctionnalitesObject extends DataObject
    {
        protected $_dataClass   = 'FonctionnalitesData';
        protected $_dataId      = 'FD_ID';
        protected $_dataColumns = array(
            'FD_Image'          => 'FD_Image'            
        );

        protected $_indexClass   = 'FonctionnalitesIndex';
        protected $_indexId = 'FI_ID';
        protected $_indexLanguageId = 'FI_LangId';
        protected $_indexColumns    = array(
            'FI_Title' => 'FI_Title',
            'FI_SubTitle' => 'FI_SubTitle',
            'FI_SmallDescription' => 'FI_SmallDescription',
            'FI_Description' => 'FI_Description'
        );
        
/*
        public function getAllFonctionnalites($langId = null, $array = true, $id = null)
        {   
            $select = parent::getAll($langId, FALSE);            
            $select->order('EEM_Order ASC');
            
            $tableReq = $this->_db->fetchAll($select);            
            return $tableReq;

        }*/
        
        /*
        public function getDetailEmploye($langId = null, $array = true, $id = null)
        {
            $select = $this->_db->select();           

            $select->from('Equipe_EquipeManagement', array('EEM_ID', 'EEM_Name', 'EEM_ImageBand'))
                    ->join('Equipe_EquipeManagementIndex','Equipe_EquipeManagementIndex.EEMI_EquipeManagementID = Equipe_EquipeManagement.EEM_ID' )
                    ->where('Equipe_EquipeManagementIndex.EEMI_LanguageID = ?', $langId )
                    ->where('Equipe_EquipeManagement.EEM_ID = ?', $id );            
            
            $employe = $this->_db->fetchAll($select);
            
            return $employe;

        }*/

    }
    
?>
