<?php
/**
 * Module Catalog
 * Management of the products for Logiflex.
 *
 * @category  Application_Module
 * @package   Application_Module_Catalog
 *

 * @license   Empty
 * @version   $Id: SubCategoriesObject.php 1367 2013-12-27 04:19:31Z ssoares $id
 */

/**
 * Manage data from product line.
 *
 * @category  Application_Module
 * @package   Application_Module_Catalog
 *

 * @license   Empty
 * @version   $Id: SubCategoriesObject.php 1367 2013-12-27 04:19:31Z ssoares $id
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
    protected $_searchColumns = array(
        'data' => array(),
        'index' => array(
            'SCI_Name'
        )
    );

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

        /**
     * The category data by sub-category
     *
     * @param int  $subCatId Id of the sub-category
     * @param bool $value     Define the type of data to return.
     *
     * @return int | Zend_db_Select
     */
    public function getCategoryId($subCatId, $value = false)
    {
        $catProd = array();

        if ($subCatId == 0)
        {
            throw new Exception('Pas de sous-categorie en paramètre.');
        }

        $select = $this->_db->select()
                ->from(
                    array('_data' => $this->_oDataTableName),
                    'SC_CategoryID')
                ->where('SC_ID = ?', $subCatId);

        if ($value)
            return $subCatProd = $this->_db->fetchOne($select);
        else
            return $select;

    }

    /**
     * The list of sub categories
     *
     * @param int $categoryId Id of the category
     *
     * @return array | Zend_db_Select
     */
    public function getSubCatByCategory($categoryId, $array = false, $langId = 1)
    {
        $subCatProd = array();


        $select = $this->_db->select()
                ->from(array('_data' => $this->_oDataTableName), $this->_dataId)
                ->joinLeft(array('index' => $this->_oIndexTableName),
                        '_data.' . $this->_dataId . ' = index.' . $this->_indexId,
                        array($this->_indexId, 'SCI_Name', 'SCI_ValUrl'))
                ->where('index.' . $this->_indexLanguageId . ' = ?', $langId);

        if ($categoryId != 0)
        {
            $select->where('SC_CategoryID = ?', $categoryId);
        }

        if ($array)
            return $subCatProd = $this->_db->fetchAll($select);
        else
            return $select;

    }

    /**
     * Fetch the id of a sub-category according the formatted string from URL.
     *
     * @param string $string
     *
     * @return int Id of the searched category
     */
    public function getIdByName($string)
    {
        $select = $this->_db->select()
                ->from($this->_oDataTableName, 'SC_ID')
                ->joinLeft(
                        $this->_oIndexTableName,
                        $this->_dataId . " = " . $this->_indexId,
                        '')
                ->where("SCI_ValUrl LIKE ?", "%" . $string . "%")
                ;

        $id = $this->_db->fetchRow($select);

        return $id['SC_ID'];
    }
}
