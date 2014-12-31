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
    public function breadcrumbCatalog($level = 1, $showHome = true, $langId = null, $options = array())
    {

        if( $langId == null )
            $langId = Zend_Registry::get('languageID');

        $_baseUrl = Zend_Registry::get('baseUrl');

        $_breadcrumb = array();
        $_first = true;

        $pathInfo  = $this->view->request->getPathInfo();
        $oProducts = new CatalogCollection();

        $oProducts->setActions($pathInfo);
        $oProducts->getDataByName();

        $subCatId = null;
        $catId    = $oProducts->getCatId();
        $prodId   = $oProducts->getProdId();
        $details = Cible_FunctionsPages::getPageDetails($this->view->currentPageID, $langId);
        $pageLink = "<a href='{$_baseUrl}/{$details['PI_PageIndex']}'>{$details['PI_PageTitle']}</a>";
        if ($catId == null && $subCatId == null && $prodId == null)
        {
//            $_breadcrumb = $this->view->breadcrumb(true);
            $_breadcrumb[0] = $this->view->pageTitle()->toString(null, null, true);
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
                $link = $_first ? $details['PI_Name']: "<a href='{$_baseUrl}/{$pathElemts[0]}/{$pathElemts[1]}/{$pathElemts[2]}' class='{$_class}'>{$details['PI_Name']}</a>";
                array_push($_breadcrumb, $link);
                if( $_first ){$_first = false;}
            }

            if($subCatId)
            {
                $_class = '';
                $oCatalog = new CatalogCollection();
                $buildOnObj = $oCatalog->getBuildSubMenuOn();
                $object = new $buildOnObj();
                $details = $object->populate($subCatId, $langId);
                if( $_first ){$_class = 'current_page';}
                $link = $_first ? $details[$object->getTitleField()] : "<a href='{$_baseUrl}/{$pathElemts[0]}/{$pathElemts[1]}' class='{$_class}'>{$details[$object->getTitleField()]}</a>";
                array_push($_breadcrumb, $link);
                array_push($_breadcrumb, $pageLink);
                if( $_first ){$_first = false;}
            }

            if($catId)
            {
                $_class = '';
                $object = new CatalogCategoriesObject();
                $details = $object->populate($catId, $langId);
                if( $_first ){$_class = 'current_page';}
                $link = $_first ? $details['CCI_Name'] : "<a href='{$_baseUrl}/{$pathElemts[0]}' class='{$_class}'>{$details['CCI_Name']}</a>";
                array_push($_breadcrumb, $link);
                if( $_first ){$_first = false;}

            }

            return $_breadcrumb;

        }
    }
}