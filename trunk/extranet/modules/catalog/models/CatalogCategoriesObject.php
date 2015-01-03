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
 * @version   $Id: CatalogCategoriesObject.php 1303 2013-10-25 20:37:48Z ssoares $id
 */

/**
 * Manage data from colletion table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: CatalogCategoriesObject.php 1303 2013-10-25 20:37:48Z ssoares $id
 */
class CatalogCategoriesObject extends DataObject
{
    protected $_dataClass       = 'CatalogCategoriesData';
    protected $_indexClass      = 'CatalogCategoriesIndex';
    protected $_indexLanguageId = 'CCI_LanguageID';
    protected $_constraint      = '';
    protected $_foreignKey      = 'CC_ParentId';
    protected $_titleField      = 'CCI_Name';
    protected $_valurlField     = 'CCI_ValUrl';
    protected $_orderBy         = array('CC_Seq ASC', 'CC_ID');
    protected $_query;
    protected $_addSubFolder    = true;
    protected $_name            = 'categories';
    protected $_id = 0;

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getTitleField()
    {
        return $this->_titleField;
    }

    public function insert($data, $langId)
    {
        $data = parent::_formatInputData($data);
        return parent::insert($data, $langId);
    }

    public function save($id, $data, $langId)
    {
        $data = parent::_formatInputData($data);
        return parent::save($id, $data, $langId);
    }

    /**
     *
     * @return type
     */
    public function _categoriesSrc($addDefault = false)
    {

        $data = $this->getList();
        if (!$addDefault)
            unset($data[0]);

        return $data;
    }

    public function getTitleValue()
    {
        $title = '';
        $langId = Cible_Controller_Action::getDefaultEditLanguage();
        if ($this->_id > 0){
            $data = $this->populate($this->_id, $langId);
            $title = $data[$this->_titleField];
        }

        return $title;
    }

    private function _formatOutputData(array $data)
    {
        foreach ($data as $field => $values)
        {
            switch ($field)
            {
                case '':
                    $data[$field] = explode(',', $values);

                    break;

                default:
                    break;
            }
        }

        return $data;
    }

    public function populate($id, $langId)
    {
        $data = $this->_formatOutputData(parent::populate($id, $langId));

        return $data;
    }

    public function buildCatalogMenu($menuCatalog, $options = array())
    {
        $langId = Cible_Controller_Action::getDefaultEditLanguage();
        $defaultCatId = 0;
        $defaultCat = "";
        $link = "";
        $opt = array('pageId' => $menuCatalog['PageID'], 'moduleId' => 14);
        $blocks = Cible_FunctionsBlocks::getBlocksFromRelatedPage($opt);
        if(Zend_Registry::isRegistered('defaultCategory')
            && !is_null(Zend_Registry::get('defaultCategory')))
        {
            $defaultCatId = Zend_Registry::get('defaultCategory');
            $tmpCat       = $oCategories->populate($defaultCatId, $langId);
            if(empty ($tmpCat['CI_ValUrl']))
                $tmpCat['CI_ValUrl'] = "";
            $defaultCat = $tmpCat['CI_ValUrl'];
        }elseif (isset($blocks['blocks'])){
            $blockId = current(array_keys($blocks['blocks']));
            $param = Cible_FunctionsBlocks::getBlockParameter($blockId, 1);
            if ($param > 0){
                $defaultCatId = $param;
            }
        }

        $this->_query = $this->getAll($langId, false);
        $this->_query->where('CC_Online = ?', 1);
        if ($defaultCatId > 0)
            $this->_query->where($this->_foreignKey . ' = ?', $defaultCatId);

        $categories = $this->_db->fetchAll($this->_query);

        $catalog = array(
            'ID' => $menuCatalog['ID'],
            'Title' => $menuCatalog['Title'],
            'PageID' => '',
            'Link' => $link . $defaultCat,
            'Placeholder' => 0,
            'menuImage' => '',
            'loadImage' => '',
            'menuImgAndTitle' => '',
            'child' => array()
        );


        return $this->_getTree($categories, $catalog, $langId);
    }

    private function _getTree($categories, $catalog, $langId)
    {
        foreach ($categories as $category)
        {
            $childs = array();
            $name = $category[$this->_valurlField];
            $linkCat = $link . $name;

            $menu = array(
                'ID' => $category[$this->_datald],
                'Title' => $category[$this->_titleField],
                'PageID' => '',
                'Link' => $linkCat,
                'menuImage' => $category['CC_imageCat'],
                'loadImage' => 0,
                'menuImgAndTitle' => 1,
                'Placeholder' => 1);

            if ($options['nesting'] > 1)
            {
                $qry = $this->getAll($langId, false);
                $qry->where($this->_foreignKey . ' = ?', $category[$this->_dataId]);
                $qry->where('CC_Online = ?', 1);
                $children = $this->_db->fetchAll($qry);
                $childs[] = $this->_getTree ($children, $catalog, $langId);
            }
            $menu['child'] = $childs;

            $catalog['child'][] = $menu;
        }

        return $catalog;
    }

    public function getList($withKey = false, $langId = null, $noDefault = false)
    {
        $list = array();
        if (is_null($langId))
            $langId = Cible_Controller_Action::getDefaultEditLanguage();

        if (!$noDefault)
            $list[''] = Cible_Translation::getCibleText('form_select_default_label');

        $temp = array();
        if ($withKey){
            $this->_list= array();
            $this->_query = $this->getAll($langId, false);
            $this->_query->where($this->_foreignKey . ' = ?', 0);
            $data = $this->findData(null, true);
            foreach ($data as $x => $values){
                $this->_getParents($values);
            }
            $list = $list + $this->_list;

        }else{
            $data  = $this->getAll($langId);
            foreach ($data as $values){
                $list[$values[$this->_dataId]] = $values[$this->_titleField];
            }
        }

        return $list;
    }

    private function _getParents($values, $name = array(), $list = array())
    {
        $parentId = $values[$this->_dataId];
        $children = $this->findData(array($this->_foreignKey => $parentId));
        if (!empty($children))
        {
            $name[] = $values[$this->_titleField];
            foreach($children as $child)
            {
                $this->_getParents($child, $name);
            }
        }elseif(empty($name)){
            $this->_list[$values[$this->_dataId]] = $values[$this->_titleField];
        }else{
            $this->_list[implode(' > ', $name)][$values[$this->_dataId]] = $values[$this->_titleField];
        }
    }
}