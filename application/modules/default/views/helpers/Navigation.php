<?php
/**
* Build the main menu to display
*
* The system finds pages and each of these child pages to display in the main menu order by position
*
* PHP versions 5
*
* LICENSE: 
*
* @category   Views Helpers
* @package    Default
* @author     Alexandre Beaudet <alexandre.beaudet@ciblesolutions.com>
* @copyright  2009 CIBLE Solutions d'Affaires
* @license    http://www.ciblesolutions.com
* @version    CVS: <?php $ ?> Id:$
*/

class Zend_View_Helper_Navigation
{
    /**
    * Principal function to build the main menu
    *
    * 1- Get all the main pages (pages that are not associated with a parent)
    * 2- Call a recursive function to get all the child pages 
    * 3- Return the result to the main view
    *
    * @author     Alexandre Beaudet <alexandre.beaudet@ciblesolutions.com>
    */
    public function navigation()
    {
        // get all first level page (parentid = 0)
        $Pages = Zend_Registry::get("db");
        $Select = $Pages->select()
                        ->from('PagesIndex')
                        ->join('Pages', 'Pages.P_ID = PagesIndex.PI_PageID')
                        ->where('PagesIndex.PI_LanguageID = ?', Zend_Registry::get("languageID"))
                        ->where('Pages.P_ParentID = ?', '0')
                        ->where('PagesIndex.PI_Status = ?', 'en ligne')
                        ->order('Pages.P_Position');
        $Rows = $Pages->fetchAll($Select);
        
        // build the menu to display
        $menu  = "<ul class='navigation'>";
        
        foreach($Rows as $Row){
            $menu .= "<li><a href='".Zend_Registry::get('baseUrl')."/".$Row['PI_PageIndex']."'>".$Row['PI_PageTitle']."</a>";
            // get all childrens of the page
            $menu .=  $this->findChildrensPage($Row['P_ID']);
            $menu .= "</li>";
        }
        
        $menu .= "</ul>";
        
        return $menu;
    }
    
    /**
    * Recursive function that find all children's page of a parent page
    *
    * 1- Get all the children's page (pages that are associated with the parentID)
    * 2- Call the function again to find the children's children recursively 
    * 3- Return the result to the previous call
    *
    * @author     Alexandre Beaudet <alexandre.beaudet@ciblesolutions.com>
    */
    public function findChildrensPage($ParentID)
    {
        // get all childrens associated with the parentID
        $Pages = Zend_Registry::get("db");
        $Select = $Pages->select()
                        ->from('PagesIndex')
                        ->join('Pages', 'Pages.P_ID = PagesIndex.PI_PageID')
                        ->where('Pages.P_ParentID = ?', $ParentID)
                        ->where('PagesIndex.PI_LanguageID = ?', Zend_Registry::get("languageID"))
                        ->where('Pages.P_ParentID <> ?', '0')
                        ->where('PagesIndex.PI_Status = ?', 'en ligne')
                        ->order('Pages.P_Position');
        
        $Rows = $Pages->fetchAll($Select);
        
        // continue to build the main menu...
        $menu = "";
        if(count($Rows) > 0){
               $menu  = "<ul>";
               foreach($Rows as $Row){
                   $menu .= "<li><a href='".Zend_Registry::get('baseUrl')."/".$Row['PI_PageIndex']."'>".$Row['PI_PageTitle']."</a>";
                   // get all childrens of the children 
                   $menu .=  $this->findChildrensPage($Row['P_ID']);
                   $menu .= "</li>";
               }
               $menu .= "</ul>";
        }
        
        return $menu;        
    }
}
