<?php
/**
 * Module Catalog
 * Management of the Items.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: CatalogObject.php 1191 2013-05-14 02:45:42Z ssoares $id
 */

/**
 * Manage data from items table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: CatalogObject.php 1191 2013-05-14 02:45:42Z ssoares $id
 */
class CatalogObject extends DataObject
{
    protected $_dataClass   = 'ProductsData';
    protected $_dataId      = '';
    protected $_constraint      = '';
    protected $_foreignKey      = '';

    protected $_buildSubMenuOn = "CatalogCategoriesObject";

    public function getBuildSubMenuOn()
    {
        return $this->_buildSubMenuOn;
    }

    /**
     * Builds folder to manage images and files according to the current website.
     *
     * @param string  $module The current module name.
     * @param string  $path Path relative to the current site.
     *
     * @return void
     */
    public function buildBasicsFolders($module, $path)
    {
        $imgPath = $path . '/data/images/' . $module ;
        if (!is_dir($path . '/data/files/order'))
            mkdir($path . '/data/files/order');
        if (!is_dir($path . '/data/files/order/export'))
            mkdir($path . '/data/files/order/export');
        if (!is_dir($imgPath))
        {
            mkdir ($imgPath);
            mkdir ($imgPath . '/tmp' );
            mkdir ($imgPath . '/categories' );
            mkdir ($imgPath . '/categories/tmp' );
            mkdir ($imgPath . '/sub-categories' );
            mkdir ($imgPath . '/sub-categories/tmp' );
            mkdir ($imgPath . '/products' );
            mkdir ($imgPath . '/products/tmp' );
            mkdir ($imgPath . '/items' );
            mkdir ($imgPath . '/items/tmp' );
        }
    }
}