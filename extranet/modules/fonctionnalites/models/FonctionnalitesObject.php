<?php
/**
 * Module Utilities
 * Management of the references data.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Utilities
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: BannerImageObject.php 42 2011-06-02 00:54:28Z ssoares $
 */

/**
 * Manage data from references table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_References
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: BannerImageObject.php 42 2011-06-02 00:54:28Z ssoares $
 */
class FonctionnalitesObject extends DataObject
{
    protected $_dataClass   = 'FonctionnalitesData';

    protected $_indexClass      = 'FonctionnalitesIndex';
    protected $_indexLanguageId = 'FI_LangId';

    protected $_constraint      = '';
    protected $_foreignKey      = '';

    public function imageCollection($id = 0)
    {
        (array) $array = array();

        if($id>0){
            $groups = $this->getAll(null,true,$id);
        }
        else{
            $groups = $this->getAll();
        }
        return $groups;
    }
    
    public function _postauthorsSrc(){
        $obj = new ReferencesObject();
        return $list = $obj->getListValues('blogueAuthor', $this->_defaultEditLanguage);
    }
    
     public function _poststatusSrc(){
         $status = Cible_FunctionsGeneral::getStatus();  
         $returnArray = array();
         foreach($status as $key => $value){
             $returnArray[$value['S_ID']] = $value['S_Code'];
         }         
         return $returnArray;
    }    
    
    public function _postcategorySrc(){
        $returnArray = array();
        $obj = new CategoriesObject();
        $tmp = $obj->getAll($this->_defaultEditLanguage,false);        
        $tmp->where("C_ModuleID = ?", "1010");     
        $return = $this->_db->fetchAll($tmp);
        foreach($return as $key => $value){
             $returnArray[$value['C_ID']] = $value['Title'];
         } 
        
        return $returnArray;
    }
    
    
    public function buildBasicsFolders($module, $path)
    {
        $imgPath = $path . '/data/images/' . $module ;
        if (!is_dir($imgPath))
        {
            mkdir ($imgPath);
            mkdir ($imgPath . '/tmp' );
        }
    }
    
    
}