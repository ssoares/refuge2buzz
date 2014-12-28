<?php
/**
 * Module Catalog
 * Management of the products for Logiflex.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: SubCategoriesObject.php 563 2011-08-22 03:23:22Z ssoares $id
 */

/**
 * Manage data from product line.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: SubCategoriesObject.php 563 2011-08-22 03:23:22Z ssoares $id
 */
class SubCategoriesObject extends DataObject
{
    protected $_dataClass   = 'SubCategoriesData';
//    protected $_dataId      = '';
//    protected $_dataColumns = array();

    protected $_indexClass      = 'SubCategoriesIndex';
//    protected $_indexId         = '';
    protected $_indexLanguageId = 'SCI_LanguageID';
    protected $_constraint      = 'SC_CategoryID';
//    protected $_indexColumns    = array();

    /**
     * Build the list of subcategories for a dropdown select.
     * Data are grouped by category.
     *
     * @param int $langId
     *
     * @return array
     */
    public function subcatCollection($langId)
    {
        (array) $array = array();

        $select = $this->getAll($langId, false);

        $select->join(
                'Catalog_CategoriesData',
                'SC_CategoryID = CC_ID',
                array())
            ->join('Catalog_CategoriesIndex', 'CC_ID = CCI_CategoryID', 'CCI_Name')
            ->where('CCI_LanguageID = ?', $langId)
            ->order('CCI_Name')
        ;

        $subcat =$this->_db->fetchAll($select);

        foreach ($subcat as $data)
        {
            $key = $data['CCI_Name'];
            //If cat not in array add it as an array
            if(!array_key_exists($key, $array))
            {
                $array[$key] = array($data['SC_ID'] => $data['SCI_Name']);

            }
            //Else Add values product id and product name into the subcat array
            else
            {
                $array[$key][$data['SC_ID']] = $data['SCI_Name'];
            }

        }

        return $array;
    }

    public function buildCatalogMenu($menuCatalog, $options = array())
    {
        $tree   = array();
        $langId = Zend_Registry::get('languageID');
        $defaultCat = "";
        $link = "";

//        $oCategories = new CatalogCategoriesObject();
//
//        if(Zend_Registry::isRegistered('defaultCategory')
//            && !is_null(Zend_Registry::get('defaultCategory')))
//        {
//            $defaultCatId = Zend_Registry::get('defaultCategory');
//            $tmpCat       = $oCategories->populate($defaultCatId, $langId);
//            if(empty ($tmpCat['CI_ValUrl']))
//                $tmpCat['CI_ValUrl'] = "";
//            $defaultCat   = $tmpCat['CI_ValUrl'];
//        }
//        $categories = $oCategories->getAll($langId);
//        $catalog    = Array(
//            'ID'     => $menuCatalog['ID'],
//            'Title'  => $menuCatalog['Title'],
//            'PageID' => '',
//            'Link'   => $link . $defaultCat,
//            'Placeholder' => 0,
//            'menuImage' => '',
//            'loadImage' => '',
//            'menuImgAndTitle' => '',
//            'child' => array()
//        );

//        foreach ($categories as $category)
//        {
//            $childs  = array();
//            $id      = $category['CC_ID'];
//            $name    = Cible_FunctionsGeneral::formatValueForUrl($category['CCI_Name']);
//            $linkCat = $link . $name;
//
//            $menu['ID']          = $category['CC_ID'];
//            $menu['Title']       = $category['CCI_Name'];
//            $menu['PageID']      = '';
//            $menu['Link']        = $linkCat;
//            $menu['menuImage']   = $category['CC_imageCat'];
//            $menu['loadImage']   = 1;
//            $menu['menuImgAndTitle'] = 1;
//
//            $menu['Placeholder'] = '2';
            $subCategories = $this->getAll($langId);
//            var_dump($subCategories);
//            exit;
//            if ($options['nesting'] > 1)
//            {
                foreach ($subCategories as $subCat)
                {
                    $name = "/" . Cible_FunctionsGeneral::formatValueForUrl($subCat['SCI_Name']);
//                    $linkSubCat = $linkCat . $name;
                    $linkSubCat =  $name;

                    $child['ID']          = $subCat['SC_ID'];
                    $child['Title']       = $subCat['SCI_Name'];
                    $child['PageID']      = '';
                    $child['Link']        = $linkSubCat;
                    $child['menuImage']   = '';
                    $child['loadImage']   = 0;
                    $child['menuImgAndTitle'] = 0;
                    $child['Placeholder'] = '2';

                    $childs[] = $child;
                    $name     = '';
                }
    }
}