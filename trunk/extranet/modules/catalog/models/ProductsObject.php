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
 * @version   $Id: ProductsObject.php 1461 2014-02-07 18:20:51Z ssoares $id
 */

/**
 * Manage data from products table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: ProductsObject.php 1461 2014-02-07 18:20:51Z ssoares $id
 */
class ProductsObject extends DataObject
{
    protected $_dataClass       = 'ProductsData';
    protected $_indexClass      = 'ProductsIndex';
    protected $_indexLanguageId = 'PI_LanguageID';
    protected $_foreignKey      = 'P_CategoryID';
    protected $_titleField      = 'PI_Name';
    protected $_valurlField     = 'PI_ValUrl';
    protected $_orderBy         = array('P_Seq ASC');
    protected $_addSubFolder    = true;
    protected $_name            = 'products';

    public function getName()
    {
        return $this->_name;
    }

    public function _categorySrc()
    {
        $oCat = new CatalogCategoriesObject();
        $list = $oCat->getList(true);

        return $list;
    }

    public function insert($data, $langId)
    {
        $data = $this->_formatInputData($data);
        $id = parent::insert($data, $langId);
        if (isset($data['moreImg']))
        {
            $oProdImg = new ProductsImagesObject();
            $oProdImg->setProductId($id)->insert($data['moreImg'], $langId);
        }
        if (isset($data['productsSet']))
        {
            $obj = new ProductsAssociationObject();
            $obj->setAssociation($id, $data['productsSet'], 'insert');
        }
        return $id;
    }

    public function save($id, $data, $langId)
    {
        $data = $this->_formatInputData($data);
        if (isset($data['moreImg']))
        {
            $oProdImg = new ProductsImagesObject();
            $oProdImg->setProductId($id)
                ->deleteByProductId()
                ->insert($data['moreImg'], $langId);
        }
        $obj = new ProductsAssociationObject();
        if (isset($data['productsSet']))
            $obj->setAssociation($id, $data['productsSet'], 'save');
        else
            $obj->setAssociation($id, array(), 'delete');

        $saved = parent::save($id, $data, $langId);

        return $saved;
    }

    public function delete($id)
    {
        $oProdImg = new ProductsImagesObject();
        $oProdImg->setProductId($id)->deleteByProductId();
        parent::delete($id);
    }

    public function getRelatedImages($id)
    {
        $oProdImg = new ProductsImagesObject();
        $data = $oProdImg->setProductId($id)->getAll(1, true);

        return $data;
    }

    public function getAssociations($nameAssoc, $id, $langId = 1)
    {
        $related = array();
        $relatedArray = array('products' => 'ProductsAssociationObject');
        $name = $relatedArray[$nameAssoc];
        $association = new $name();
        $selectAssociation = $association->getAll($langId, false);
        $selectAssociation->where($association->getDataId() . " = ?", $id);
        $associationFind = $this->_db->fetchAll($selectAssociation);

        foreach ($associationFind as $assocData)
            $related[] = $assocData[$association->getForeignKey()];

        return $related;
    }

}