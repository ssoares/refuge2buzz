<?php

/**
 * Module Catalog
 * Controller for the backend administration of Logiflex.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: ProductsController.php 1372 2013-12-27 22:07:54Z ssoares $id
 */

/**
 * Manage actions for the products list.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: ProductsController.php 1372 2013-12-27 22:07:54Z ssoares $id
 */
class Catalog_ProductsController extends Cible_Controller_Block_Abstract
{

    protected $_moduleID = 14;
    protected $_defaultAction = 'list-products';
    protected $_defaultRender = 'list';
    protected $_moduleTitle = 'catalog';
    protected $_name = 'products';
    protected $_paramId = 'productID';
    protected $_imageSrc = 'P_Photo';
    protected $_catalogImageFolder;
    protected $_imageFolder;
    protected $_rootImgPath;

    public function init()
    {
        parent::init();
        $dataImagePath = "../../"
                . $this->_config->document_root
                . "/data/images/";

//        $this->_catalogImageFolder = $dataImagePath
//                                        . $this->_moduleTitle . "/";

        $this->_imageFolder = $dataImagePath
                . $this->_moduleTitle . "/"
                . $this->_name . "/";

        $this->_rootImgPath = Zend_Registry::get("www_root")
                . "/data/images/"
                . $this->_moduleTitle . "/"
                . $this->_name . "/";
    }

    public function addAction()
    {
        // web page title
        $this->view->title = "Ajout d'un produit";
//        $this->_registry->currentEditLanguage = $this->_defaultEditLanguage;

        // variables
        $returnAction = $this->_getParam('return');
        $baseDir = $this->view->baseUrl();
        $cancelUrl = $baseDir . "/"
                . $this->_moduleTitle . "/"
                . $this->_name . "/"
                . $this->_defaultAction . "/";

        if ($returnAction)
            $returnUrl = $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $returnAction;
        else
            $returnUrl = $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $this->_defaultAction . "/";

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();
                if ($formData[$this->_imageSrc] <> "")
                    if ($formData[$this->_imageSrc] <> "")
                        $imageSrc = $this->_rootImgPath
                                . "tmp/mcith/mcith_"
                                . $formData[$this->_imageSrc];
            }

            $langId = $this->_defaultEditLanguage;
            $oCollections = new ProductsObject();
            $collectionData = $oCollections->getAll($langId, true);

            $this->view->assign('collectionData', $collectionData);

            // generate the form
            $form = new FormProducts(
                            array(
                                'moduleName' => $this->_moduleTitle . '/' . $this->_name,
                                'baseDir' => $baseDir,
                                'cancelUrl' => $cancelUrl,
                                'imageSrc' => $imageSrc,
                                'imgField' => $this->_imageSrc,
                                'dataId' => '',
                                'isNewImage' => true
                            )
            );

            $this->view->form = $form;

            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();

                if ($form->isValid($formData))
                {
                    $oData = new ProductsObject();
                    $newData = array_merge(
                                    $formData['productFormLeft'],
                                    $formData['productFormRight'],
                                    $formData['productFormBotPub'],
                                    $formData['productFormBotPro']);

                    $formattedName = Cible_FunctionsGeneral::formatValueForUrl($newData['PI_Name']);
                    $newData['PI_ValUrl'] = $formattedName;

                    $recordID = $oData->insert(
                                    $newData,
                                    $this->_registry->currentEditLanguage
                    );

                    // ADD Association
                    if (array_key_exists("collectionSet", $formData))
                    {
                        foreach ($formData['collectionSet'] as $collectionID)
                        {
                            $oAssociation = new ProductsAssociationData();
                            $associateData = $oAssociation->createRow();
                            $associateData->AP_MainProductID = $recordID;
                            $associateData->AP_RelatedProductID = $collectionID;

                            $associateData->save();
                        }
                    }
                    /* IMAGES */
                    mkdir($this->_imageFolder . $recordID)
                            or die("Could not make directory");
                    mkdir($this->_imageFolder . $recordID . "/tmp")
                            or die("Could not make directory");

                    if ($form->getSubForm('productFormRight')->getValue($this->_imageSrc) <> '')
                    {
                        $config = Zend_Registry::get('config')->toArray();
                        $srcOriginal = $this->_imageFolder . "tmp/" . $newData[$this->_imageSrc];
                        $originalMaxHeight = $config[$this->_moduleTitle]['image']['original']['maxHeight'];
                        $originalMaxWidth = $config[$this->_moduleTitle]['image']['original']['maxWidth'];
                        $originalName = str_replace(
                                        $newData[$this->_imageSrc],
                                        $originalMaxWidth
                                        . 'x'
                                        . $originalMaxHeight
                                        . '_'
                                        . $newData[$this->_imageSrc],
                                        $newData[$this->_imageSrc]
                        );


                        $srcMedium = $this->_imageFolder
                                . "tmp/medium_"
                                . $newData[$this->_imageSrc];
                        $mediumMaxHeight = $config[$this->_moduleTitle]['image']['medium']['maxHeight'];
                        $mediumMaxWidth = $config[$this->_moduleTitle]['image']['medium']['maxWidth'];
                        $mediumName = str_replace(
                                        $newData[$this->_imageSrc],
                                        $mediumMaxWidth
                                        . 'x'
                                        . $mediumMaxHeight
                                        . '_'
                                        . $newData[$this->_imageSrc],
                                        $newData[$this->_imageSrc]
                        );

                        $srcThumb = $this->_imageFolder
                                . "tmp/thumb_"
                                . $newData[$this->_imageSrc];
                        $thumbMaxHeight = $config[$this->_moduleTitle]['image']['thumb']['maxHeight'];
                        $thumbMaxWidth = $config[$this->_moduleTitle]['image']['thumb']['maxWidth'];
                        $thumbName = str_replace(
                                        $newData[$this->_imageSrc],
                                        $thumbMaxWidth
                                        . 'x'
                                        . $thumbMaxHeight
                                        . '_'
                                        . $newData[$this->_imageSrc],
                                        $newData[$this->_imageSrc]
                        );

                        copy($srcOriginal, $srcMedium);
                        copy($srcOriginal, $srcThumb);

                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcOriginal,
                                            'maxWidth' => $originalMaxWidth,
                                            'maxHeight' => $originalMaxHeight)
                        );
                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcMedium,
                                            'maxWidth' => $mediumMaxWidth,
                                            'maxHeight' => $mediumMaxHeight)
                        );
                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcThumb,
                                            'maxWidth' => $thumbMaxWidth,
                                            'maxHeight' => $thumbMaxHeight)
                        );

                        rename($srcOriginal, $this->_imageFolder . $recordID . "/" . $originalName);
                        rename($srcMedium, $this->_imageFolder . $recordID . "/" . $mediumName);
                        rename($srcThumb, $this->_imageFolder . $recordID . "/" . $thumbName);
                    }

                    if (isset($formData['submitSaveClose']))
                        $this->_redirect($returnUrl);
                    else
                        $this->_redirect(
                            $this->_moduleTitle . "/"
                            . $this->_name . "/edit/productID/" . $recordID);
                }
                else
                {
                    if (array_key_exists("collectionSet", $formData))
                        $this->view->assign('collectionChoice', $formData['collectionSet']);
                    else
                        $this->view->assign('collectionChoice', array());

                    $form->populate($formData);
                }
            }
        }
    }

    public function editAction()
    {
        // web page title
        $this->view->title = "Modification d'un produit";
        // variables
        $recordID = (int) $this->_getParam($this->_paramId);
        $returnAction = $this->_getParam('return');
        $baseDir = $this->view->baseUrl();
        $lang = $this->_getParam('lang');
        if (!$lang)
            $this->_registry->currentEditLanguage = $this->_defaultEditLanguage;

        $langId = (int) $this->_registry->currentEditLanguage;

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $cancelUrl = $baseDir . "/"
                    . $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $this->_defaultAction . "/";

            if ($returnAction)
                $returnUrl = $this->_moduleTitle . "/"
                        . $this->_name . "/"
                        . $returnAction;
            else
                $returnUrl = $this->_moduleTitle . "/"
                        . $this->_name . "/"
                        . $this->_defaultAction . "/";

            $oCollections = new ProductsObject();
            $collectionData = $oCollections->getAll($langId, true);

            $this->view->assign('collectionData', $collectionData);

            // get products details
            $oData = new ProductsObject();
            $record = $oData->populate($recordID, $langId);

            // image src.
            $config         = Zend_Registry::get('config')->toArray();
            $thumbMaxHeight = $config[$this->_moduleTitle]['image']['thumb']['maxHeight'];
            $thumbMaxWidth  = $config[$this->_moduleTitle]['image']['thumb']['maxWidth'];

            if (!empty($record[$this->_imageSrc]))
            {
                $this->view->assign(
                        'imageUrl',
                        $this->_rootImgPath
                        . $recordID . "/"
                        . str_replace(
                                $record[$this->_imageSrc],
                                $thumbMaxWidth
                                . 'x'
                                . $thumbMaxHeight
                                . '_'
                                . $record[$this->_imageSrc],
                                $record[$this->_imageSrc])
                );
                $isNewImage = 'false';
            }

            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();

                if ($formData[$this->_imageSrc] <> $record[$this->_imageSrc])
                {
                    if ($formData[$this->_imageSrc] == "")
                        $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                    else
                        $imageSrc = $this->_rootImgPath
                                . $recordID
                                . "/tmp/mcith/mcith_"
                                . $formData[$this->_imageSrc];

                    $isNewImage = 'true';
                }
                else
                {
                    if ($record[$this->_imageSrc] == "")
                        $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                    else
                        $imageSrc = $this->_rootImgPath
                                . $recordID . "/"
                                . str_replace(
                                        $record[$this->_imageSrc],
                                        $thumbMaxWidth
                                        . 'x'
                                        . $thumbMaxHeight . '_'
                                        . $record[$this->_imageSrc],
                                        $record[$this->_imageSrc]);
                }
            }
            else
            {
                if (empty($record[$this->_imageSrc]))
                    $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                else
                    $imageSrc = $this->_rootImgPath
                            . $recordID . "/"
                            . str_replace(
                                    $record[$this->_imageSrc],
                                    $thumbMaxWidth
                                    . 'x'
                                    . $thumbMaxHeight . '_'
                                    . $record[$this->_imageSrc],
                                    $record[$this->_imageSrc]);
            }

            // generate the form
            $form = new FormProducts(
                            array(
                                'moduleName' => $this->_moduleTitle . '/' . $this->_name,
                                'baseDir' => $baseDir,
                                'cancelUrl' => $cancelUrl,
                                'imageSrc' => $imageSrc,
                                'imgField' => $this->_imageSrc,
                                'dataId' => $recordID,
                                'isNewImage' => true
                            )
            );

            $this->view->form = $form;

            // action
            if (!$this->_request->isPost())
            {

                $association = new ProductsAssociationData();
                $selectAssociation = $association->select();
                $selectAssociation->where("AP_MainProductID = ?", $recordID);
                $associationFind = $association->fetchAll($selectAssociation)->toArray();

                $collectionChoice = array();
                foreach ($associationFind as $association)
                {
                    $collectionChoice[] = $association['AP_RelatedProductID'];
                }
                $this->view->assign('collectionChoice', $collectionChoice);

                $oItem = new ItemsObject();
                $itemList = $oItem->getAssociatedItems($recordID, $langId);

                $this->view->assign('renderItemsList', $itemList);

                $form->populate($record);
            }
            else
            {
                $formData = $this->_request->getPost();
                if ($form->isValid($formData))
                {
                    $oData = new ProductsObject();
                    $newData = array_merge(
                                    $formData['productFormLeft'],
                                    $formData['productFormRight'],
                                    $formData['productFormBotPub'],
                                    $formData['productFormBotPro']);

                    $formattedName = Cible_FunctionsGeneral::formatValueForUrl($newData['PI_Name']);
                    $newData['PI_ValUrl'] = $formattedName;

                    $oData->save($recordID, $newData, $langId);

                    // DELETE ASSOCIATION
                    $association = new ProductsAssociationData();
                    $where = "AP_MainProductID = " . $recordID;
                    $association->delete($where);

                    // ADD Association
                    if (array_key_exists("collectionSet", $formData))
                    {
                        foreach ($formData['collectionSet'] as $collectionID)
                        {
                            $oAssociation = new ProductsAssociationData();
                            $associateData = $oAssociation->createRow();
                            $associateData->AP_MainProductID = $recordID;
                            $associateData->AP_RelatedProductID = $collectionID;

                            $associateData->save();
                        }
                    }
                    $newImage = $form->getSubForm('productFormRight')->getValue('isNewImage');

                    if ($newImage && $newData[$this->_imageSrc] <> '')
                    {
                        $config = Zend_Registry::get('config')->toArray();
                        $srcOriginal = $this->_imageFolder
                                . $recordID
                                . "/tmp/"
                                . $newData[$this->_imageSrc];
                        $originalMaxHeight = $config[$this->_moduleTitle]['image']['original']['maxHeight'];
                        $originalMaxWidth = $config[$this->_moduleTitle]['image']['original']['maxWidth'];
                        $originalName = str_replace(
                                        $newData[$this->_imageSrc],
                                        $originalMaxWidth
                                        . 'x'
                                        . $originalMaxHeight . '_'
                                        . $newData[$this->_imageSrc],
                                        $newData[$this->_imageSrc]);


                        $srcMedium = $this->_imageFolder
                                . $recordID . "/tmp/medium_"
                                . $newData[$this->_imageSrc];

                        $mediumMaxHeight = $config[$this->_moduleTitle]['image']['medium']['maxHeight'];
                        $mediumMaxWidth = $config[$this->_moduleTitle]['image']['medium']['maxWidth'];
                        $mediumName = str_replace(
                                        $newData[$this->_imageSrc],
                                        $mediumMaxWidth
                                        . 'x'
                                        . $mediumMaxHeight . '_'
                                        . $newData[$this->_imageSrc],
                                        $newData[$this->_imageSrc]);

                        $srcThumb = $this->_imageFolder
                                . $recordID
                                . "/tmp/thumb_"
                                . $newData[$this->_imageSrc];
                        $thumbMaxHeight = $config[$this->_moduleTitle]['image']['thumb']['maxHeight'];
                        $thumbMaxWidth = $config[$this->_moduleTitle]['image']['thumb']['maxWidth'];
                        $thumbName = str_replace(
                                        $newData[$this->_imageSrc],
                                        $thumbMaxWidth
                                        . 'x'
                                        . $thumbMaxHeight . '_'
                                        . $newData[$this->_imageSrc],
                                        $newData[$this->_imageSrc]);

                        copy($srcOriginal, $srcMedium);
                        copy($srcOriginal, $srcThumb);

                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcOriginal,
                                            'maxWidth' => $originalMaxWidth,
                                            'maxHeight' => $originalMaxHeight)
                        );
                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcMedium,
                                            'maxWidth' => $mediumMaxWidth,
                                            'maxHeight' => $mediumMaxHeight)
                        );
                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcThumb,
                                            'maxWidth' => $thumbMaxWidth,
                                            'maxHeight' => $thumbMaxHeight)
                        );

                        rename($srcOriginal,
                                $this->_imageFolder
                                . $recordID . "/" . $originalName);
                        rename($srcMedium,
                                $this->_imageFolder
                                . $recordID . "/" . $mediumName);
                        rename($srcThumb,
                                $this->_imageFolder
                                . $recordID . "/" . $thumbName);
                    }

                    if ($formData['Status'] == 0)
                        $formData['Status'] = 2;

                    // redirect

                    if (isset($formData['submitSaveClose']))
                        $this->_redirect($returnUrl);
                    else
                        $this->_redirect(
                            $this->_moduleTitle . "/"
                            . $this->_name . "/edit/productID/" . $recordID);
                }
                else
                {
                    if (array_key_exists("collectionSet", $formData))
                        $this->view->assign('collectionChoice', $formData['collectionSet']);
                    else
                        $this->view->assign('collectionChoice', array());

                    $form->populate($record);
                }
            }
        }
    }

    public function deleteAction()
    {
        $this->view->title = "Suppression d'un produit";

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'manage', true))
        {
            // Get the e id
            $id = (int) $this->_getParam($this->_paramId);
            // generate the form
            $returnUrl = $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $this->_defaultAction . "/";

            $this->view->assign(
                    'return',
                    $this->view->baseUrl() . "/" . $returnUrl
            );

            $oData = new ProductsObject();
            $select = $oData->getAll(null, false, $id);
            $data = $this->_db->fetchRow($select);

            $this->view->product = $data;

            if ($this->_request->isPost())
            {
                $del = $this->_request->getPost('delete');
                if ($del && $id > 0)
                {
                    $oData->delete($id);
                    Cible_FunctionsGeneral::delFolder($this->_imageFolder . $id);
                    // DELETE ASSOCIATION
                    $association = new ProductsAssociationData();
                    $where = "AP_MainProductID = " . $id;
                    $association->delete($where);
                }

                $this->_redirect($returnUrl);
            }
        }
    }

    public function listProductsAction()
    {
        // web page title
        $this->view->title = "Produits";

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $tables = array(
                'Catalog_ProductsData' => array(
                    'P_ID'),
                'Catalog_ProductsIndex' => array(
                    'PI_ProductIndexID',
                    'PI_LanguageID',
                    'PI_Name'
                )
            );
            $field_list = array(
                'P_ID' => array('width' => '50px'),
                'PI_Name' => array('width' => '300px'),
            );

            $this->view->params = $this->_getAllParams();
            $pageID = $this->_getParam('pageID');
            $lang = $this->_getParam('lang');
            if (!$lang)
            {
                $this->_registry->currentEditLanguage = $this->_defaultEditLanguage;
                $langId = $this->_defaultEditLanguage;
            }
            else
            {
                $langId = Cible_FunctionsGeneral::getLanguageID($lang);
                $this->_registry->currentEditLanguage = $langId;
            }
            $lines = new ProductsObject();
            $select = $lines->getAll($langId, false);
            $select->order('PI_Name ASC');

            $commands = array();
            if ($langId == $this->_defaultEditLanguage)
                $commands = array(
                    $this->view->link($this->view->url(
                            array(
                                'controller' => $this->_name,
                                'action' => 'add'
                            )
                        ), $this->view->getCibleText('button_add'), array('class' => 'action_submit add')
                    )
                );
            $options = array(
                'commands' => $commands,
                'disable-export-to-excel' => 'false',
//                    'filters' => array(
//                        'productline-status-filter' => array(
//                            'label' => 'Filtre 1',
//                            'default_value' => null,
//                            'associatedTo' => 'S_Code',
//                            'choices' => array(
//                                '' => $this->view->getCibleText('filter_empty_status'),
//                                'online' => $this->view->getCibleText('status_online'),
//                                'offline' => $this->view->getCibleText('status_offline')
//                            )
//                        )
//                    ),
                'action_panel' => array(
                    'width' => '50',
                    'actions' => array(
                        'edit' => array(
                            'label' => $this->view->getCibleText('button_edit'),
                            'url' => $this->view->baseUrl() . "/"
                            . $this->_moduleTitle . "/"
                            . $this->_name
                            . "/edit/"
                            . $this->_paramId
                            . "/%ID%",
                            'findReplace' => array(
                                'search' => '%ID%',
                                'replace' => 'P_ID'
                            )
                        ),
                        'delete' => array(
                            'label' => $this->view->getCibleText('button_delete'),
                            'url' => $this->view->baseUrl() . "/"
                            . $this->_moduleTitle . "/"
                            . $this->_name
                            . "/delete/"
                            . $this->_paramId
                            . "/%ID%/"
                            . $pageID,
                            'findReplace' => array(
                                'search' => '%ID%',
                                'replace' => 'P_ID'
                            )
                        )
                    )
                )
            );

            $mylist = New Cible_Paginator($select, $tables, $field_list, $options);
            $this->view->assign('mylist', $mylist);
        }
    }

    /**
     * Create a dorpdown list for the association to do
     * Retrieve parameters from url parameters sent via ajax.
     *
     * @return void
     */
    public function ajaxAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $associationAction = $this->_getParam('associationAction');
        $associationID = $this->_getParam('associationID');
        $associationSetID = $this->_getParam('associationSetID');
        if ($associationAction == "new")
        {

            $optionsData = array();
            if ($associationSetID == "collection")
            {
                $oData = new ProductsObject();
                $optionsData = $oData->getAll($this->_registry->languageID, true);
            }

            $newElement = "";
            if (count($optionsData) >= 1)
            {
                $newElement .= "<select name='" . $associationSetID . "Set[" . $associationID . "]' class='selectAssociationOption'>";
                $newElement .= "<option value='-1'>"
                        . $this->view->getCibleText("association_set_selectOne")
                        . "</option>";

                foreach ($optionsData as $option)
                {
                    $newElement .= "<option value='" . $option['P_ID'] . "'>" . $option['PI_Name'] . "</option>";
                }

                $newElement .= "</select>";
            }

            echo(Zend_Json::encode(array('newElement' => $newElement)));
        }
    }

    /**
     * Export data according to given parameters.
     *
     * @return void
     */
    public function toExcelAction()
    {
        $this->type = 'XLS';
        $this->filename = 'Produit.xls';

        $lines = new ProductsObject();

        $this->tables = array(
            'Catalog_ProductsData' => $lines->getDataColumns(),
            'Catalog_ProductsIndex' => $lines->getIndexColumns()
        );

        $this->view->params = $this->_getAllParams();

        $this->fields = array_merge($lines->getIndexColumns(),$lines->getDataColumns());

        $this->filters = array();


        $pageID = $this->_getParam('pageID');
        $langId = $this->_registry->languageID;


        $this->select = $lines->getAll($langId, false);

        parent::toExcelAction();
    }

    public function formatProductNameAction()
    {
        $oProduct = new ProductsObject();

        $select = $oProduct->getAll(null, false);
        $select->where('PI_ValUrl is NULL');
        $db = Zend_Registry::get('db');

        $products = $db->fetchAll($select);

        foreach ($products as $product)
        {
            $name = $product['PI_Name'];
            $formatted = Cible_FunctionsGeneral::formatValueForUrl($name);

            $data = array(
                'PI_ValUrl' => $formatted
                );
            $oProduct->save($product['P_ID'], $data, $product['PI_LanguageID']);

        }
    }


    public function traverseHierarchyAction()
    {
        $result = $this->traverseHierarchy();
    }
    public function traverseHierarchy($path = "", $maxWidth = 170, $maxHeight = 170)
    {
        if(empty($path))
        $path = $_SERVER['DOCUMENT_ROOT']
            .Zend_Registry::get('www_root').'/data/images/catalog/products';

        $returnArray = array();
        $tmp = array();
        $dir = opendir($path);

        while (($file = readdir($dir)) !== false)
        {
            if ($file[0] == '.' || $file == 'tmp')
                continue;

            $fullPath = $path . '/'. $file;
            //Trouver la plus grande image.
            if (!is_dir($fullPath))
            {
                $folder = dirname($fullPath);
                $tmp = explode('_', $file);
                $dim = explode('x', $tmp[0]);
                unset($tmp[0]);
                $prodId = substr($folder,(strrpos($folder, '/') + 1), 3 );
                if ($pid != $prodId)
                {
                    $pid = $prodId;
                    $dt1 = filemtime($fullPath);
                    $data = array(
                        'P_Photo' => implode('_',$tmp)
                    );

                }
                else
                {
                    $dt2 = filemtime($fullPath);
                    if($dt1 < $dt2)
                        $data = array(
                            'P_Photo' => implode('_',$tmp)
                        );
                }

                 $oProduct = new ProductsObject();
                $oProduct->save($prodId, $data, 1);
                //construire le nom le chemin de l'image
//                if ($dim[0] > $maxWidth && $dim[1] > $maxHeight)
//                {
//                    $srcThumb = $path . '/' . $maxWidth.'x'.$maxHeight.'_'.$tmp[1];
//                    //remplir le tableau image
//                    $image = array(
//                        'src'       => $srcThumb,
//                        'maxWidth'  => $maxWidth,
//                        'maxHeight' => $maxHeight
//                    );
//                    copy($fullPath, $srcThumb);
//                    resampled($image);
//                }
            }
            else // your if goes here: if(substr($file, -3) == "jpg") or something like that
                $this->traverseHierarchy($fullPath);
        }

    }
}