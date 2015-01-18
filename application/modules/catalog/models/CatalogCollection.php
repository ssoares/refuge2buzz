<?php
/**
 * Module Catalog
 * Management of the products.
 *
 * @category  Apploication_Module
 * @package   Application_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: CatalogCollection.php 1684 2014-09-09 13:13:58Z jpbernard $
 */

/**
 * Manage data from all the catalog tables.
 *
 * @category  Application_Module
 * @package   Application_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: CatalogCollection.php 1684 2014-09-09 13:13:58Z jpbernard $
 */
class CatalogCollection
{
    const MODULE_NAME = 'catalog';
    /**
     * The database object instance
     *
     * @var Zend_Db
     */
    protected $_db;
    /**
     * Current language
     *
     * @var int
     */
    protected $_currentLang;
    /**
     * Block id
     *
     * @var int
     */
    protected $_blockID = null;
    /**
     * Parameters of the block
     *
     * @var array
     */
    protected $_blockParams = array();

    protected $_actions = array();

    protected $_keywords = array();
    protected $_filter   = array();
    protected $_catId    = 0;
    protected $_prodId   = 0;
    protected $_limit    = 9;
    protected $_type     = 'list.phtml';
    protected $_bonus    = false;
    protected $_oCategory = null;
    protected $_oProducts = null;
    protected $_buildSubMenuOn = "CatalogCategoriesObject";
    protected $_sortby = array('P_Seq ASC','P_Number ASC');

    public function getBuildSubMenuOn()
    {
        return $this->_buildSubMenuOn;
    }

    /**
     * Fetch the parameter value
     *
     * @param int $param_name Number identifying the parameter
     *
     * @return string
     */
    public function getBlockParam($param_name)
    {
        return $this->_blockParams[$param_name];
    }

    /**
     * Getter for hasBonus. The product allows to cumulate bonus point.
     *
     * @return bool
     */
    public function getBonus()
    {
        return $this->_bonus;
    }

    /**
     * Return the number of product by page
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Return the category id
     *
     * @return int
     */
    public function getCatId()
    {
        return $this->_catId;
    }

    /**
     * Return the product id
     *
     * @return int
     */
    public function getProdId()
    {
        return $this->_prodId;
    }

    /**
     * Return the parameters array
     *
     * @return array
     */
    public function getBlockParams()
    {
        return $this->_blockParams;
    }

    /**
     * Return the filter attribute.
     *
     * @return array
     */
    public function getFilter()
    {
        return $this->_filter;
    }
    /**
     * Return the actions attribute
     *
     * @return array
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Class constructor
     *
     * @param int $blockID Id of the block. Default value = null
     */
    public function __construct($params = array())
    {
        if(isset($params['lang'])){
            $this->_currentLang = $params['lang'];
        }
        else{
            $this->_currentLang = Zend_Registry::get('languageID');
        }
        $this->_db           = Zend_Registry::get('db');

        $this->setParameters($params);

    }

    /**
     * Set parameters given in the url
     * @param array $params Parameters from url to set to build le product list.
     * @return void
     */
    public function setParameters($params = array())
    {
        foreach ($params as $property => $value)
        {
            if ($property == 'BlockID')
                $property = 'blockID';

            $methodName = 'set' . ucfirst($property);

            if (property_exists($this, '_' . $property)
                && method_exists($this, $methodName))
            {
                $this->$methodName($value);
            }
        }
    }

    public function setBlockID($value)
    {
        $this->_blockID = $value;
        $_params = Cible_FunctionsBlocks::getBlockParameters($value);

        foreach ($_params as $param)
        {
            $this->_blockParams[$param['P_Number']] = $param['P_Value'];
        }
    }

    public function setActions($value)
    {
        $exclude = array('index', 'page', 'keywords', 'collection', 'product');
        $include = array('collection', 'product');
        $tmpArray = explode("/", trim($value, '/'));

        foreach ($exclude as $value)
        {
            $key = array_search($value, $tmpArray);
            if ($key)
                unset($tmpArray[$key]);
            if(($value == 'page' || $value == 'keywords') && $key)
                unset($tmpArray[$key + 1]);
        }

        $lastVal = end($tmpArray);
        if ($lastVal == "")
            array_pop($tmpArray);

        $this->_actions = $tmpArray;
    }
    public function setCatId($value)
    {
        $this->_catId = $value;
    }

    public function setType($value)
    {
        $this->_type = $value;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setLimit($value)
    {
        $this->_limit = $value;
    }

    public function setKeywords($value)
    {
        if (!empty($value)
            && $value != Cible_Translation::getCibleText('form_search_catalog_keywords_label'))
            $this->_keywords = explode(" ", trim($value));
    }

    public function setFilter($filters)
    {
        if (is_array($filters))
        {
            foreach ($filters as $value)
            {
                $data = explode('_', $value);
                $this->_filter[$data[0]] = $data[1];
            }
        }
        else
        {
            $data = explode('_', $filters);
            $this->_filter[$data[0]] = $data[1];
        }
    }

    public function getOCategory()
    {
        return $this->_oCategory;
    }

    public function getOProducts()
    {
        return $this->_oProducts;
    }

    /**
     * Get the list of the products for the current category
     *
     * @param int $limit
     *
     * @return array
     */
    public function getList()
    {
        $results   = array();
        $this->getDataByName();

        $this->_oProducts = new ProductsObject();
        $this->_oCategory = new CatalogCategoriesObject();
        $this->_oCategory->setOrderBy('CC_Seq');

        if (isset($this->_blockParams['1']))
            Zend_Registry::set('defaultCategory', $this->_blockParams['1']);

        if (!$this->_prodId)
        {
            // If no category selected, set the default one.
            if (!$this->_catId && !$this->_keywords && isset ($this->_blockParams['1'])){
                $categoryId = $this->_blockParams['1'];
            }else{
                $categoryId = $this->_catId;
            }
            Zend_Registry::set('catId_',$categoryId);
            $catQry = $this->_oCategory->getAll($this->_currentLang,false);
            $hasChildren = $this->_oCategory->setQuery($catQry)->hasChildren($categoryId);

            if ($hasChildren){
                $select = $this->_oCategory->getQuery();
            }else{
                $qry = $this->_oCategory->getAll(
                        $this->_currentLang,
                        false,
                        $categoryId);
                $select = $this->_oProducts->setQuery($qry)
                    ->getProducts($this->_currentLang, false);
                $select->order('P_Seq ASC');
                $this->_type = 'list-products.phtml';
            }

            if (count($this->_keywords))
                $this->_setFilterByKeyword($select);

            if (count($this->_filter))
            {
                $filterClause = "";
                foreach ($this->_filter as $key => $value)
                {
                    $select = $this->_addFilterQuery($key, $value, $select);
                }
            }

            $results = $this->_db->fetchAll($select);
        }
        else
        {
            $product      = $this->_oProducts->getAll($this->_currentLang, true, $this->_prodId);
            $tmpArray     = $product[0];
            $dataCategory = $this->_oCategory->getAll($this->_currentLang, true, $tmpArray['P_CategoryId']);
            $category     = $dataCategory[0];
            $results['data'] = array_merge($tmpArray, $category);

            $oItems = new ItemsObject();
            $items  = $oItems->getItemsByProductId($this->_prodId);
            $results['items'] = $items;

            $oImages  = new ProductsImagesObject();
            $oImages->setProductId($this->_prodId);
            $results['images'] = $oImages->getAll($this->_currentLang);

            $oAssocProd = new ProductsAssociationObject();
            $relations  = $oAssocProd->getAll($this->_currentLang, true, $this->_prodId );
            $tmp = array();
            $relatedProd = array();

            foreach ($relations as $relProd)
            {
                if ($relProd['AP_RelatedProductID'] != -1)
                {
                    $tmp = $this->_oProducts->populate($relProd['AP_RelatedProductID'], $this->_currentLang);
                    $category  = $this->_oCategory->getAll($this->_currentLang, true, $tmp['P_CategoryId']);
                    $this->_oCategory->getDataCatagory($this->_currentLang, false, $tmp['P_CategoryId']);
                    $stringUrl = '/';
                    $stringUrl .= implode('/', $this->_oCategory->setCategoriesLink(true)->getLink());
                    $stringUrl .= '/' . $tmp['PI_ValUrl'];
                    $tmp['link'] = $stringUrl;
                    $tmpCat    = $category[0];
                    $tmp       = array_merge($tmp, $tmpCat);

                    $relatedProd[]  = $tmp;
                }
            }

            $results['relatedProducts'] = $relatedProd;
        }


        return $results;
    }

    /**
     * Set filters for search by keywords.
     *
     * @param Zend_Db_Select $select The begining of the query to complete.
     *
     * @return void
     */
    private function _setFilterByKeyword(Zend_Db_Select $select)
    {
        $source = array(
            'Products',
//            'Types',
//            'Clientele',
            'SubCategories',
//            'Items',
            'CatalogCategories'
        );
        $excludeTables = array(
            'Catalog_SousCategoriesData',
            'Catalog_ProductsData',
            'Catalog_CategoriesData',
            'Catalog_SousCategoriesIndex',
            'Catalog_ProductsIndex',
            'Catalog_CategoriesIndex'
        );
        $where = "";
        foreach ($source as $table)
        {
            $oData  = $table . 'Object';
            $object = new $oData();
            $object->setExcludeTables($excludeTables);

            if (strlen($where) > 0)
                $where .= ' OR ';

            $where .= $object->keywordExist(
                    $this->_keywords,
                    $select,
                    $this->_currentLang);
        }

        $select->where($where);
    }

    public function getDataByName()
    {
        $lastVal = end($this->_actions);
        $oCat = new CatalogCategoriesObject();
        $categoryId = $oCat->getIdByName($lastVal);
        if ($categoryId > 0)
            $this->_catId = $categoryId;
        elseif (!$this->_catId)
        {
            $oProd = new ProductsObject();
            $this->_prodId = $oProd->getIdByName($lastVal);
        }
        elseif (!$this->_prodId)
        {
            $defaultCat = Cible_FunctionsBlocks::getBlockParameter($this->_blockID, 1);
            if($defaultCat)
                $this->_catId = $defaultCat;
        }

    }

    protected function _addFilterQuery($key, $value, $select)
    {
        $db    = Zend_Registry::get('db');
        $where = "";
        switch ($key)
        {
            case 'fabrication':
                if ($value == 'madeInQuebec')
                    $select->where('P_MadeInQc = ?', 1);
//                    $where = $db->quoteInto();
                break;
            case 'collections':
                $select->where('SCI_ValUrl = ?', $value);
                break;
            case 'typeVetements':
//                ->joinLeft('Catalog_TypesData', 'P_TypeID = T_ID', array())
//                    ->joinLeft('Catalog_TypesIndex', 'TI_TypeID = T_ID', array())
                $select->where('TI_ValUrl = ?', $value);
                break;
            case 'clienteles':
//                ->joinLeft('Catalog_ClienteleData', 'P_ClienteleID = CL_ID', array())
//                    ->joinLeft('Catalog_ClienteleIndex', 'CLI_ClienteleID = CL_ID', array())
                $select->where('CLI_ValUrl = "' . $value .'"' );

                break;
            default:
                break;
        }

        return $select;
    }

    public function getProductsUrl(){
        $productsURL   = array();
        $productsURLValid = array();
        $oCategory = new CatalogCategoriesObject();
        $oCategories = $oCategory->getAll($this->_currentLang);

        foreach ($oCategories as $oCat){
            $this->setCatId($oCat['CC_ID']);
            $productsURL = $this->getList();
            array_push($productsURLValid,$oCat['CCI_ValUrl']);
            foreach ($productsURL as $proURL){
                $str = $proURL['CCI_ValUrl'] . "/collection/" . $proURL['SCI_ValUrl'] . "/product/" . $proURL['PI_ValUrl'];
                array_push($productsURLValid,$str);
            }
        }


       return $productsURLValid;
    }

    public function getDetails($id, $itemId = 0, $resume = false)
    {
        $products   = array();

        $oProduct = new ProductsObject();
        $oItem    = new ItemsObject();

        $products['data'] = $oProduct->populate(
                    $id,
                    $this->_currentLang);

        if($itemId)
            $products['items'] = $oItem->populate($itemId, $this->_currentLang);

        return $products;
    }
}
