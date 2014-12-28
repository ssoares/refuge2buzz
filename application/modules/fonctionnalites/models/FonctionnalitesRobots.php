<?php
/*****
 * 
 *  Return the string that will be put in the robots.txt
 * 
 ****/
class FonctionnalitesRobots extends DataObject
{
    protected $_dataClass   = 'FonctionnalitesData';
    
    protected $_indexClass      = 'FonctionnalitesIndex';
    protected $_indexLanguageId = 'FI_LangId';

    protected $_specificAction = array('details');  // this are the action that need a specific treatment
    
    public function getXMLFilesString($path = "", $title = "")
    {      
        $db = Zend_Registry::get('db');
        $xmlString = "";
        (array) $array = array();


        $select1 = $db->select()
                ->distinct()
                ->from('Languages');
        $langs = $db->fetchAll($select1);


        foreach ($langs as $lang){            
           $xmlString .= $path . "/" . $title . "/index/site-map/lang/" . $lang['L_ID'] . "\n";
        }
        return $xmlString;
    }
    
    public function getXMLFile($path = "", $lang = ""){
         $arrayForXML = array();
         $db = Zend_Registry::get('db');
         $select3 = $db->select()
            ->distinct()
            ->from('Equipe_EquipeManagement');
        $TeamRows = $db->fetchAll($select3);      
        foreach ($TeamRows as $TeamRow){
            $arrayURL = array();
            $details_page = "";
            if($lang==1){
                $details_page = "les-cibles/details/employeeId/";
            }
            else{
                $details_page = "team/results/employeeId/";
            }
            array_push($arrayURL, $path . "/" . $details_page . $TeamRow['EEM_ID']);
            array_push($arrayURL,"0.4");
            array_push($arrayURL,"monthly");
            array_push($arrayURL,date("Y-m-d H:m:s"));
            array_push($arrayForXML,$arrayURL);
        }
        return $arrayForXML;
    }
}