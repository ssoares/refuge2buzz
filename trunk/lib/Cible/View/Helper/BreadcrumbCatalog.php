<?php
/**
 * Cible
 *
 *
 * @category   Cible
 * @package    Cible_View
 * @subpackage Cible_View_Helper
 * @copyright  Copyright (c) 2009 Cible Solutions d'affaires
 *             (http://www.ciblesolutions.com)
 * @version    $Id:
 */

/**
 * Build the breadcrumb for catalog pages
 * 
 * @category   Cible
 * @package    cible_View
 * @subpackage Cible_View_Helper
 * @copyright  Copyright (c) 2009 Cible Solutions d'affaire
 *             (http://www.ciblesolutions.com)
 */
class Cible_View_Helper_BreadcrumbCatalog extends Zend_View_Helper_Abstract
{
    /**
     * Build the breadcrumd for the catalog page.
     *
     * @param int $lang  <Optional> Id of the current language
     * 
     * @return string 
     */
    public function breadcrumbCatalog($level = 1, $showHome = true, $langId = null)
    {
       
        if( $langId == null )
            $langId = Zend_Registry::get('languageID');

        $_baseUrl = Zend_Registry::get('baseUrl');

        $_breadcrumb = array();
        $_first = true;
        
        $pathInfo  = $this->view->request->getPathInfo();
        $oProducts = new ProductsCollection();

        $oProducts->setActions($pathInfo);
        $oProducts->getDataByName();

        $catId    = $oProducts->getCatId();
        $subCatId = $oProducts->getSubCatId();
        $prodId   = $oProducts->getProdId(); 
        
        if ($catId == null && $subCatId == null && $prodId == null)
        {
            $_breadcrumb = $this->view->breadcrumb(true) . "<b>" . $this->view->selectedPage . "</b>";
            return  $_breadcrumb;
        }
        else
        {
            $pathElemts = $oProducts->getActions();
           
            if($prodId)
            {
              
                $_class = '';
                $product = new ProductsObject();
                $details = $product->populate($prodId, $langId);               
                if( $_first ){$_class = 'current_page';}
                $link = $_first ? "<b>" . $details['PI_Name'] . "</b>" : "<a href='{$_baseUrl}/{$this->view->selectedPage}/{$pathElemts[0]}/{$pathElemts[1]}/{$pathElemts[2]}' class='{$_class}'>{$details['PI_Name']}</a>";
                array_push($_breadcrumb, $link);
                if( $_first ){$_first = false;}
            }
            
            if($subCatId)
            {
                $_class = '';
                $object = new SubCategoriesObject();
                $details = $object->populate($subCatId, $langId);
                if( $_first ){$_class = 'current_page';}
                $link = $_first ? "<b>" . $details['SCI_Name'] . "</b>" : "<a href='{$_baseUrl}/{$this->view->selectedPage}/{$pathElemts[0]}/{$pathElemts[1]}' class='{$_class}'>{$details['SCI_Name']}</a>";
                array_push($_breadcrumb, $link);
                if( $_first ){$_first = false;}
            }
            if($catId)
            {
                $_class = '';
                $object = new CatalogCategoriesObject();
                $details = $object->populate($catId, $langId);
                if( $_first ){$_class = 'current_page';}
                $link = $_first ? "<b>" . $details['CCI_Name'] . "</b>" : "<a href='{$_baseUrl}/{$this->view->selectedPage}/{$pathElemts[0]}' class='{$_class}'>{$details['CCI_Name']}</a>";
                array_push($_breadcrumb, $link);
                if( $_first ){$_first = false;}

            }

            $details = Cible_FunctionsPages::getPageDetails($this->view->currentPageID, $langId);

            $link = $_first ? '' : "<a href='{$_baseUrl}/{$details['PI_PageIndex']}' class='{$_class}'>{$details['PI_PageTitle']}</a>";
            array_push($_breadcrumb, $link);

            if($showHome){
                $homeDetails = Cible_FunctionsPages::getHomePageDetails();
                $link = "<a href='{$_baseUrl}/{$homeDetails['PI_PageIndex']}' class='{$_class}'>". $homeDetails['PI_PageTitle'] . "</a>";
                array_push($_breadcrumb, $link);
            }

            $_breadcrumb = array_reverse($_breadcrumb);
            //var_dump($_breadcrumb);
//            for($i=0;$i<$level;$i++){
//                array_splice($_breadcrumb,$i+1,1);
//            }
            // add the > after the breadcrumb when only on item is found
            if( count($_breadcrumb) == 1 )
                return "{$_breadcrumb[0]} > ";
            else
                return implode( ' > ', $_breadcrumb);
        
        }
    }
}
?>
