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
 * @version    $Id: SubMenu.php 1303 2013-10-25 20:37:48Z ssoares $
 */

/**
 * Manage menus for frontend
 *
 * @category   Cible
 * @package    cible_View
 * @subpackage Cible_View_Helper
 * @copyright  Copyright (c) 2009 Cible Solutions d'affaire
 *             (http://www.ciblesolutions.com)
 */
class Cible_View_Helper_SubMenu extends Cible_View_Helper_Tree
{

    /**
     * Name of the current page. Allows to set selected css style.
     *
     * @var string
     */
    protected $_selectedPage;
    /**
     * Id of the current page. Allows to set selected css style for parent menu..
     *
     * @var string
     */
    protected $_selectedPageId;
    /**
     * Parameter to enable/disable children displaying.
     *
     * @var boolean
     */
    protected $_disable_nesting;
    /**
     * Allows to define an alternative id value if the same menu is used more
     * than once.<br/>
     * Add parentAltId in options array
     *
     * @var string
     */
    protected $_parent_alt_id = "parentid-";
    /**
     * Id true add li tag before and after the menu element for specific CSS
     * (i.e. allows to set round background corners)
     * Given thru the options array
     *
     * @var boolean
     */
    protected $_addEnclosure = false;
    /**
     * First level menu to set the selected css class when the current page
     * belongs to one of its submenu.
     *
     * @var int
     */
    protected $_parentsMenuId = array();
    protected $_buildOnCatalog = false;

    /**
     * Set default values and the first level container (ul)
     *
     * @param Mixed $menu    If String: Fecth menu data according its name.<br/>
     *                       If Array: It must contain the menu tree.
     *
     * @param array $options Options to manage menu behaviour<br />
     *                       Ex: disable_nesting => true, parentAltId => (string)
     *
     * @return string html code to display the menu and is children
     */
    public function subMenu($options = array())
    {


        if (isset($options['addEnclosure']))
            $this->_addEnclosure = $options['addEnclosure'];
        if (isset($options['buildOnCatalog']))
            $this->_buildOnCatalog = $options['buildOnCatalog'];

        if ($this->view->selectedPage)
            $this->_selectedPage = $this->view->selectedPage;
        elseif (Zend_Registry::isRegistered('selectedPage'))
            $this->_selectedPage = Zend_Registry::get('selectedPage');
        else
        {
            $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
            $this->_selectedPage = $params['controller'];
            if ($params['controller'] == 'index')
                $this->_selectedPage = Cible_FunctionsPages::getPageNameByID(1);
        }

        $oPages = new PagesObject();
        $pageData = $oPages->pageIdByController($this->_selectedPage);
        $this->_selectedPageId = $pageData['P_ID'];

        if(empty($this->_selectedPageId)){
            var_dump($this->_selectedPage, $this->_selectedPageId);
            exit;
        }

        $menuData = $oPages->getRelatedMenu($this->_selectedPageId);

        $parentId = 0;
        if(!$menuData){

            $parentId = $oPages->getParentRelatedID($this->_selectedPageId);
            $parentName = $oPages->getParentRelatedName($parentId);
            $menuData = $oPages->getRelatedMenu($parentId);
            $this->_selectedPageId = $parentId;
            $parentId = $menuData['MID_ParentID'];
            $this->view->assign('selectedPage',$parentName);
            //echo $parentName;
        }
        $_menu = new MenuObject();
        $_menu->initMenu($menuData['MID_MenuId']);

        if ($menuData['MID_ParentID'] == 0){
            $parentId = $menuData['MID_ID'];
        }
        else
        {
            $menuItem = $_menu->getMenuItemByPageId($this->_selectedPageId);
            if ($menuItem)
            {
                $this->_getParentsMenuId($menuItem, $_menu);
            }
            if($parentId == 0)
                $parentId = $this->_parentsMenuId[0];
        }
        if (!empty($options['parentId'])){
            $parentId = $options['parentId'];
        }
        if (preg_match('/useCatalog/', $menuData['MID_Style']) && $this->_buildOnCatalog)
        {
            $menuData['Placeholder'] = 2;
            $collections = new CatalogCategoriesObject();
//                $menuCatalog = $this->getMenuItemByPageId( null, 'collections');
            $catalogMenu = $collections->buildCatalogMenu($menuData, array('nesting' => 2));

            $tree = $catalogMenu['child'];
//               $first = $this->populate($menuCatalog['MID_ID']);
//               $childCombined = array();


//                   $childCombined = array_merge($catalogMenu['child'], $first);
//                   $catalog['child'] = $childCombined;
        }
        elseif(!empty ($parentId)){
            $tree = $_menu->populate($parentId);
        }
        $tree['MID_MenuID']   = $menuData['MID_MenuId'];
        $tree['MID_ParentId'] = $parentId;

        return $tree;
    }

    private function _getParentsMenuId(array $itemMenu, MenuObject $oMenu)
    {
        $tmpArray = array();
        $menuId   = $itemMenu['MID_ParentID'];
        while($menuId != 0)
        {
            $details = $oMenu->getMenuItemById($menuId);

            array_push($tmpArray, $details['MID_ID']);
            $menuId = $details['MID_ParentID'];
        }
        $tmpArray = array_reverse($tmpArray);
        array_unique($tmpArray);

        $this->_parentsMenuId = $tmpArray;
    }

}
