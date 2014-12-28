<?php
/**
 * Module Catalog
 * Controller for the backend administration.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: SubCategoriesController.php 1367 2013-12-27 04:19:31Z ssoares $id
 */

/**
 * Manage actions for the categories.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: SubCategoriesController.php 1367 2013-12-27 04:19:31Z ssoares $id
 */
class Catalog_SubCategoriesController extends Cible_Controller_Block_Abstract
{
    protected $_moduleID      = 14;
    protected $_defaultAction = 'list-sub-cat';
    protected $_moduleTitle   = 'catalog';
    protected $_name          = 'sub-categories';
    protected $_paramId       = 'subCatID';

    protected $_catalogImageFolder;
    protected $_imageFolder;
    protected $_rootImgPath;
    protected $_imageSrc = "";

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
        $this->view->title = "Ajout d'une sous-catégorie";
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
                    . $this->_defaultAction;

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();
                if (isset($formData[$this->_imageSrc]) && $formData[$this->_imageSrc] <> "")
                    if ($formData[$this->_imageSrc] <> "")
                        $imageSrc = $this->_rootImgPath
                                . "tmp/mcith/mcith_"
                                . $formData[$this->_imageSrc];
            }
            // generate the form
            $form = new FormSubCategories(
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
                    $formattedName = Cible_FunctionsGeneral::formatValueForUrl($formData['SCI_Name']);
                    $formData['SCI_ValUrl'] = $formattedName;

                    $oSubCategories = new SubCategoriesObject();
                    $recordID = $oSubCategories->insert($formData,$langId);

                    /* IMAGES */
//                    mkdir($this->_imageFolder . $recordID)
//                            or die("Could not make directory");
//                    mkdir($this->_imageFolder . $recordID . "/tmp")
//                            or die("Could not make directory");
                    if ($form->getValue($this->_imageSrc) <> '')
                    {

                        $config = Zend_Registry::get('config')->toArray();
                        $srcOriginal = $this->_imageFolder . "tmp/" . $form->getValue($this->_imageSrc);
                        $originalMaxHeight = $config[$this->_moduleTitle]['image']['original']['maxHeight'];
                        $originalMaxWidth = $config[$this->_moduleTitle]['image']['original']['maxWidth'];
                        $originalName = str_replace(
                                        $form->getValue($this->_imageSrc),
                                        $originalMaxWidth
                                        . 'x'
                                        . $originalMaxHeight
                                        . '_'
                                        . $form->getValue($this->_imageSrc),
                                        $form->getValue($this->_imageSrc)
                        );


                        $srcMedium = $this->_imageFolder
                                . "tmp/medium_"
                                . $form->getValue($this->_imageSrc);
                        $mediumMaxHeight = $config[$this->_moduleTitle]['image']['medium']['maxHeight'];
                        $mediumMaxWidth = $config[$this->_moduleTitle]['image']['medium']['maxWidth'];
                        if ($mediumMaxHeight > 0 && $mediumMaxWidth > 0)
                        {
                            $mediumName = str_replace(
                                            $form->getValue($this->_imageSrc),
                                            $mediumMaxWidth
                                            . 'x'
                                            . $mediumMaxHeight
                                            . '_'
                                            . $form->getValue($this->_imageSrc),
                                            $form->getValue($this->_imageSrc)
                            );
                            copy($srcOriginal, $srcMedium);
                        }
                        $srcThumb = $this->_imageFolder
                                . "tmp/thumb_"
                                . $form->getValue($this->_imageSrc);
                        $thumbMaxHeight = $config[$this->_moduleTitle]['image']['thumb']['maxHeight'];
                        $thumbMaxWidth = $config[$this->_moduleTitle]['image']['thumb']['maxWidth'];
                        $thumbName = str_replace(
                                        $form->getValue($this->_imageSrc),
                                        $thumbMaxWidth
                                        . 'x'
                                        . $thumbMaxHeight
                                        . '_'
                                        . $form->getValue($this->_imageSrc),
                                        $form->getValue($this->_imageSrc)
                        );

                        copy($srcOriginal, $srcThumb);

                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcOriginal,
                                            'maxWidth' => $originalMaxWidth,
                                            'maxHeight' => $originalMaxHeight)
                        );
                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcThumb,
                                            'maxWidth' => $thumbMaxWidth,
                                            'maxHeight' => $thumbMaxHeight)
                        );

                        if ($mediumMaxHeight > 0 && $mediumMaxWidth > 0)
                        {
                            Cible_FunctionsImageResampler::resampled(
                                            array(
                                                'src' => $srcMedium,
                                                'maxWidth' => $mediumMaxWidth,
                                                'maxHeight' => $mediumMaxHeight)
                            );
                            rename($srcMedium, $this->_imageFolder . $recordID . "/" . $mediumName);
                        }

                        rename($srcOriginal, $this->_imageFolder . $recordID . "/" . $originalName);
                        rename($srcThumb, $this->_imageFolder . $recordID . "/" . $thumbName);
                    }
                    $this->_redirect($returnUrl);
                }
                else
                {
                    $form->populate($formData);
                }
            }
        }
    }

    public function editAction()
    {
        // web page title
        $this->view->title = "Édition d'une sous-catégorie";
        // variables
        $recordID = $this->_getParam($this->_paramId);
        $returnAction = $this->_getParam('return');
        $baseDir = $this->view->baseUrl();
        $lang = $this->_getParam('lang');
        if (!$lang)
            $this->_registry->currentEditLanguage = $this->_defaultEditLanguage;

        $langId = $this->_registry->currentEditLanguage;

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

            // get event details
            $oSubCategories = new SubCategoriesObject();
            $data = $oSubCategories->populate($recordID, $langId);

            // image src.
            $config = Zend_Registry::get('config')->toArray();
            $imageSrc = "";
            // generate the form
            $form = new FormSubCategories(
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
                $form->populate($data);
            }
            else
            {
                $formData = $this->_request->getPost();
                if ($form->isValid($formData))
                {
                    $formattedName = Cible_FunctionsGeneral::formatValueForUrl($formData['SCI_Name']);
                    $formData['SCI_ValUrl'] = $formattedName;

                    if ($formData['isNewImage'] == 'true' && $form->getValue($this->_imageSrc) <> '')
                    {
                        $config = Zend_Registry::get('config')->toArray();
                        $srcOriginal = $this->_imageFolder
                                . $recordID
                                . "/tmp/"
                                . $form->getValue($this->_imageSrc);
                        $originalMaxHeight = $config[$this->_moduleTitle]['image']['original']['maxHeight'];
                        $originalMaxWidth = $config[$this->_moduleTitle]['image']['original']['maxWidth'];
                        $originalName = str_replace(
                                        $form->getValue($this->_imageSrc),
                                        $originalMaxWidth
                                        . 'x'
                                        . $originalMaxHeight . '_'
                                        . $form->getValue($this->_imageSrc),
                                        $form->getValue($this->_imageSrc));


                        $srcMedium = $this->_imageFolder
                                . $recordID . "/tmp/medium_"
                                . $form->getValue($this->_imageSrc);

                        $mediumMaxHeight = $config[$this->_moduleTitle]['image']['medium']['maxHeight'];
                        $mediumMaxWidth = $config[$this->_moduleTitle]['image']['medium']['maxWidth'];

                        if ($mediumMaxHeight > 0 && $mediumMaxWidth > 0)
                        {
                            $mediumName = str_replace(
                                            $form->getValue($this->_imageSrc),
                                            $mediumMaxWidth
                                            . 'x'
                                            . $mediumMaxHeight . '_'
                                            . $form->getValue($this->_imageSrc),
                                            $form->getValue($this->_imageSrc));

                            copy($srcOriginal, $srcMedium);
                        }

                        $srcThumb = $this->_imageFolder
                                . $recordID
                                . "/tmp/thumb_"
                                . $form->getValue($this->_imageSrc);
                        $thumbMaxHeight = $config[$this->_moduleTitle]['image']['thumb']['maxHeight'];
                        $thumbMaxWidth = $config[$this->_moduleTitle]['image']['thumb']['maxWidth'];
                        $thumbName = str_replace(
                                        $form->getValue($this->_imageSrc),
                                        $thumbMaxWidth
                                        . 'x'
                                        . $thumbMaxHeight . '_'
                                        . $form->getValue($this->_imageSrc),
                                        $form->getValue($this->_imageSrc));

                        copy($srcOriginal, $srcThumb);

                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcOriginal,
                                            'maxWidth' => $originalMaxWidth,
                                            'maxHeight' => $originalMaxHeight)
                        );
                        Cible_FunctionsImageResampler::resampled(
                                        array(
                                            'src' => $srcThumb,
                                            'maxWidth' => $thumbMaxWidth,
                                            'maxHeight' => $thumbMaxHeight)
                        );
                        if ($mediumMaxHeight > 0 && $mediumMaxWidth > 0)
                        {
                            Cible_FunctionsImageResampler::resampled(
                                            array(
                                                'src' => $srcMedium,
                                                'maxWidth' => $mediumMaxWidth,
                                                'maxHeight' => $mediumMaxHeight)
                            );
                            rename($srcMedium,
                                    $this->_imageFolder
                                    . $recordID . "/" . $mediumName);
                        }
                        rename($srcOriginal,
                                $this->_imageFolder
                                . $recordID . "/" . $originalName);
                        rename($srcThumb,
                                $this->_imageFolder
                                . $recordID . "/" . $thumbName);
                    }

                    $oSubCategories->save($recordID, $formData, $langId);

                    // redirect

                    if (!empty($pageID))
                        $this->_redirect(
                                $this->_moduleTitle . "/"
                                . $this->_name . "/"
                                . $this->_defaultAction . "/blockID/$blockID/pageID/$pageID");
                    else
                        $this->_redirect($returnUrl);
                }
            }
        }
    }

    public function deleteAction()
    {
        $this->view->title = "Suppression d'une sous-catégorie";

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'manage', true))
        {
            // Get the product line id
            $id = (int) $this->_getParam($this->_paramId);
            // generate the form
            $returnUrl = $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $this->_defaultAction . "/";

            $this->view->assign(
                    'return',
                    $this->view->baseUrl() . "/" . $returnUrl
            );

            $oSubCategories = new SubCategoriesObject();
            $select = $oSubCategories->getAll(null, false, $id);
            $data = $this->_db->fetchRow($select);

            $this->view->subcat = $data;

            if ($this->_request->isPost())
            {
                $del = $this->_request->getPost('delete');
                if ($del && $id > 0)
                {
                    // get all';
                    $oSubCategories->delete($id);
                }

                $this->_redirect($returnUrl);
            }
        }
    }

    public function listSubCatAction()
    {
        // web page title
        $this->view->title = "Sous-catégories";
        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $tables = array(
                'Catalog_CategoriesData' => array(
                    'CC_ID'),
                'Catalog_CategoriesIndex' => array(
                    'CCI_CategoryID',
                    'CCI_Name'),
                'Catalog_SousCategoriesData' => array(
                    'SC_ID',
                    'SC_CategoryID'),
                'Catalog_SousCategoriesIndex' => array(
                    'SCI_SousCategoryID',
                    'SCI_LanguageID',
                    'SCI_Name')
            );

            $field_list = array(
                'SC_ID' => array('width' => '50px'),
                'SCI_Name' => array('width' => '150px'),
                'CCI_Name' => array('width' => '150px'),
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

            $oSubCategories = new SubCategoriesObject();
            $select = $oSubCategories->getAll($langId, false);
            $select->joinLeft('Catalog_CategoriesData', 'CC_ID = SC_CategoryID');
            $select->joinLeft('BannerGroup', 'BG_ID = SC_BannerGroupID');
            $select->joinLeft('Catalog_CategoriesIndex', 'CC_ID = CCI_CategoryID AND CCI_LanguageID = "' . $langId . '"' );

            $commands = array();
            if ($langId == $this->_defaultEditLanguage)
                $commands = array(
                    $this->view->link($this->view->url(
                            array(
                                'controller' => $this->_name,
                                'action' => 'add'
                            )
                        ), $this->view->getCibleText('button_add_news'), array('class' => 'action_submit add')
                    )
                );
            $options = array(
                'commands' => $commands,
                'disable-export-to-excel' => 'true',
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
                                'replace' => 'SC_ID'
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
                                'replace' => 'SC_ID'
                            )
                        )
                    )
                )
            );

            $mylist = New Cible_Paginator($select, $tables, $field_list, $options);
            $this->view->assign('mylist', $mylist);
        }
    }
}