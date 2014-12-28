<?php
/**
 * Pages
 * Management of the Items.
 *
 * @category  Cible
 * @package   Cible_Pages
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: PagesObject.php 1191 2013-05-14 02:45:42Z ssoares $id
 */

/**
 * Manage data from items table.
 *
 * @category  Cible
 * @package   Cible_Pages
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: PagesObject.php 1191 2013-05-14 02:45:42Z ssoares $id
 */
class PagesObject extends DataObject
{

    protected $_dataClass   = 'Pages';
//    protected $_dataId      = '';
//    protected $_dataColumns = array();

    protected $_indexClass      = 'PagesIndex';
//    protected $_indexId         = '';
    protected $_indexLanguageId = 'PI_LanguageID';
//    protected $_indexColumns    = array();
    protected $_constraint      = 'PI_PageIndex';
    protected $_foreignKey      = '';

    public function pageIdByController($controller)
    {
        $select = $this->getAll(Zend_Registry::get('languageID'), false);

        $select->where($this->_constraint . ' = ?', $controller);

        $data = $this->_db->fetchRow($select);

        return $data;
    }

    public function getParentRelatedID($pageId){
        $select = $this->_db->select()
            ->from('Pages', array('P_ParentID'))
            ->where('P_ID = ?', $pageId)
            ->limit(1);
        $menu = $this->_db->fetchRow($select);

        return $menu['P_ParentID'];

    }

    public function getParentRelatedName($pageId){
        $select = $this->_db->select()
            ->from('PagesIndex', array('PI_PageIndex'))
            ->where('	PI_PageID = ?', $pageId)
            ->limit(1);
        $menu = $this->_db->fetchRow($select);

        return $menu['PI_PageIndex'];

    }



    public function getRelatedMenu($pageId)
    {
        $select = $this->_db->select()
            ->from('MenuItemData', array('MID_ID', 'MID_MenuId', 'MID_ParentID', 'MID_Style'))
            ->joinLeft('MenuItemIndex', 'MID_ID = MII_MenuItemDataID', array())
            ->where('MII_PageID = ?', $pageId)
            ->where('MII_LanguageID = ?', Zend_Registry::get('languageID'))
            ->order('MID_MenuID ASC')
            ->order('MID_ID ASC')
            ->limit(1);
        $menu = $this->_db->fetchRow($select);

        return $menu;
    }

    public function setIndexationData()
    {
        $Pages = new PagesIndex();
        $Select = $Pages->select()
                        ->setIntegrityCheck(false)
                        ->from('Pages')
                        ->joinLeft('PagesIndex','Pages.P_ID = PagesIndex.PI_PageID')
                ->where('PI_Status = 1')
                ->where('P_Indexation=1');

        $pageData = $Pages->fetchAll($Select)->toArray();

        $cpt = count($pageData);
        for ($i = 0; $i < $cpt; $i++)
        {
            $indexData['action'] = "add";
            $indexData['pageID'] = $pageData[$i]['PI_PageID'];
            $indexData['moduleID'] = 0;
            $indexData['contentID'] = $pageData[$i]['PI_PageID'];
            $indexData['languageID'] = $pageData[$i]['PI_LanguageID'];
            $indexData['title'] = $pageData[$i]['PI_PageTitle'];
            $indexData['text'] = '';
            $indexData['object'] = get_class();
            $indexData['link'] = '';
            $indexData['contents'] = $pageData[$i]['PI_PageTitle'];

            Cible_FunctionsIndexation::indexation($indexData);
        }
        return $this;
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
        if (!is_dir($imgPath))
        {
            mkdir ($imgPath);
            mkdir ($imgPath . '/background' );
            mkdir ($imgPath . '/background/tmp' );
            mkdir ($imgPath . '/header' );
            mkdir ($imgPath . '/header/tmp' );
        }
    }
}