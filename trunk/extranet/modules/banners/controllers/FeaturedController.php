<?php

/**
 * Module Banners
 * Controller for the backend administration.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Banners
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: FeaturedController.php 172 2011-07-05 16:51:02Z ssoares $
 *
 */

/**
 * Manage actions for the fetaured elements.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Banners
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 */
class Banners_FeaturedController extends Cible_Controller_Block_Abstract
{

    protected $_moduleID = 18;
    protected $_defaultAction = 'list';
    protected $_moduleTitle = 'banners';
    protected $_name = 'featured';
    protected $_currentAction = '';
    protected $_paramId = 'ID';
    protected $_imagesFolder;
    protected $_rootImgPath;
    protected $_rootFilePath;
    protected $_editMode = true;
    protected $_imgIndex = 'image';
    protected $_imgFeatureNumber = 4;
    protected $_addSubFolder = true;

    public function init()
    {
        $this->_currentAction = $this->_name;
        parent::init();

        $this->view->cleaction = $this->_name;
        $this->_imgIndex = 'imagefeat';


        $config = Zend_Registry::get('config')->toArray();
        //banners.imagefeat.number
        $this->_imgFeatureNumber = $config[$this->_moduleTitle][$this->_imgIndex]['number'];

    }

    public function addAction()
    {
        // web page title

        $config = Zend_Registry::get('config')->toArray();
        $useOver = $config['banners']['imagefeat']['over'];
        $useFile = $config['banners']['imagefeat']['file'];

        $videos = new VideoObject();
        $listVideo = array();
        $listVideo = $videos->getVideosList();

        $this->view->title = "Mise en vedette: ajouter";
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

        $cancelUrl = $this->view->url(array(
                    'action' => $this->_defaultAction,
                    $this->_paramId => null
                ));

            $returnUrl = str_replace($this->view->baseUrl(), '', $this->view->url(array(
                    'action' => $this->_defaultAction,
                    $this->_paramId => null
                )));

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $imageSrc = array();
            $imageSrcOver = array();
            $isNewImage = array();
            for($x = 1; $x <=$this->_imgFeatureNumber; $x++){
                $imageSrc[$x] = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                $imageSrcOver[$x] = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                $isNewImage[$x] = true;
            }

            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();


                for($x = 1; $x <=$this->_imgFeatureNumber; $x++){
                    $IF_Img = 'IF_Img' . $x;
                    $IF_ImgOver = 'IF_ImgOver' . $x;
                    if ($formData[$IF_Img] <> ""){
                        if ($formData[$IF_Img] <> ""){
                            $imageSrc[$x] = $this->_rootImgPath . "tmp/mcith/mcith_". $formData[$IF_Img];
                        }
                    }
                    if ($formData[$IF_ImgOver] <> ""){
                        if ($formData[$IF_ImgOver] <> ""){
                            $imageSrcOver[$x] = $this->_rootImgPath . "tmp/mcith/mcith_". $formData[$IF_ImgOver];
                        }
                    }
                }

            }
            // generate the form
            $form = new FormBannerFeatured(
                array(
                    'moduleName' => $this->_moduleTitle . '/' . $this->_name,
                    'baseDir'    => $baseDir,
                    'cancelUrl'  => $cancelUrl,
                    'dataId'     => '',
                    'isNewImage' => $isNewImage,
                    'imageSrc'   => $imageSrc,
                    'imageSrcOver'  => $imageSrcOver,
                    'filePath'   => $this->_rootFilePath,
                    'hasVideo'   => 0,
                    'hasStyle'   => 0,
                    'hasText2'   => 0,
                    'hasOverEffect' => $useOver,
                    'hasFileLoad' => $useFile,
                    'numberImageFeature' => $this->_imgFeatureNumber
                ),
                $listVideo
            );

            $this->view->form = $form;

            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();

                if ($form->isValid($formData))
                {
                    $oData   = new BannerFeaturedObject();
                    $newData = $formData;

                    $recordID = $oData->insert(
                            $newData, $this->_defaultEditLanguage
                    );

                    // Save image data for this banner
                    $this->_saveImgData($formData, $recordID, 'add');

                    /* IMAGES */
                    mkdir($this->_imagesFolder . $recordID)
                        or die();
                    mkdir($this->_imagesFolder . $recordID . "/tmp")
                        or die("Could not make directory");
                    $this->_editMode = false;



                    for($x = 1; $x <=$this->_imgFeatureNumber; $x++){
                        $IF_Img = 'IF_Img' . $x;
                        $IF_ImgOver = 'IF_ImgOver' . $x;
                        if ($newData[$IF_Img] <> '')
                        {
                            $this->_setImage($IF_Img,  $newData, $recordID);
                        }
                        if ($newData[$IF_ImgOver] <> '')
                        {
                            $this->_setImage($IF_ImgOver, $newData, $recordID);
                        }
                    }
                    if (isset($formData['submitSaveClose']))
                        $this->_redirect($returnUrl);
                    else
                        $this->_redirect(str_replace($this->view->baseUrl(), '', $this->view->url(array(
                                    'action' => 'edit',
                                    $this->_paramId => $recordID,
                                    'lang' => $this->languageSuffix
                                )))
                        );
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
        $videos = new VideoObject();
        $listVideo = array();
        $listVideo = $videos->getVideosList();
        //$this->view->videos = $listVideo;

        $config = Zend_Registry::get('config')->toArray();
        $useOver = $config['banners']['imagefeat']['over'];
        $useFile = $config['banners']['imagefeat']['file'];

        $recordID = $this->_getParam($this->_paramId);
        $page     = $this->_getParam('page');
        $returnAction = $this->view->params['submitSaveClose'];
        $baseDir = $this->view->baseUrl();
        $lang = $this->_getParam('lang');
        //


        $langId = $this->_registry->currentEditLanguage;

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $cancelUrl = $this->view->url(array(
                        'action' => $this->_defaultAction,
                        $this->_paramId => null,
                        'page' => $page
                    ));

                $returnUrl = str_replace($this->view->baseUrl(), '', $this->view->url(array(
                        'action' => $this->_defaultAction,
                    $this->_paramId => null,
                    'page' => $page
                    )));

            // get event details
            $oData = new BannerFeaturedObject();
            $record = $oData->loadData($recordID, $langId);


            $imageSrc = array();
            $imageSrcOver = array();
            $isNewImage = array();
            $filesPath = array();
            for($x = 1; $x <=$this->_imgFeatureNumber; $x++){
                $imageSource   = $this->_setImageSrc($record, 'IF_Img' . $x, $recordID);
                $imageSrc[$x] = $imageSource['imageSrc'];
                $imageSourceOver   = $this->_setImageSrc($record, 'IF_ImgOver' . $x, $recordID);
                $imageSrcOver[$x] = $imageSourceOver['imageSrc'];
                $isNewImage[$x]  = $imageSource['isNewImage'];

                $filesPath[$x] = $record['IFI_File'. $x];

            }


            // generate the form
            $form = new FormBannerFeatured(
                array(
                    'moduleName' => $this->_moduleTitle . '/' . $this->_name,
                    'baseDir'    => $baseDir,
                    'cancelUrl'  => $cancelUrl,
                    'dataId'     => $recordID,
                    'isNewImage' => $isNewImage,
                    'filePath'   => $this->_rootFilePath,
                    'imageSrc'   => $imageSrc,
                    'imageSrcOver'   => $imageSrcOver,
                    'hasVideo'   => 0,
                    'hasStyle'   => 0,
                    'hasText2'   => 0,
                    'filesToShowPath'   => $filesPath,
                    'hasOverEffect' => $useOver,
                    'hasFileLoad' => $useFile,
                    'numberImageFeature' => $this->_imgFeatureNumber
                ),$listVideo
            );

            $this->view->form = $form;

            if (!$this->_request->isPost())
            {
                $form->populate($record);
            }
            else
            {
                $formData = $this->_request->getPost();

                if ($form->isValid($formData))
                {
                    $newData = $formData;

                    // Save image data for this banner
                    $this->_saveImgData($formData, $recordID, 'edit');

                    for($x = 1; $x <=$this->_imgFeatureNumber; $x++){
                        if ($newData['IF_Img' . $x ] <> '')
                        {
                            $this->_setImage('IF_Img' . $x , $newData, $recordID);
                        }
                        if ($newData['IF_ImgOver' . $x ] <> '')
                        {
                            $this->_setImage('IF_ImgOver' . $x ,  $newData, $recordID);
                        }
                    }

                    $oData->save($recordID, $newData, $langId);

                    if (!empty($pageID))
                        $this->_redirect(
                            $this->_moduleTitle . "/"
                            . $this->_name . "/"
                            . $this->_defaultAction . "/blockID/$blockID/pageID/$pageID");
                    else{
                        if (isset($formData['submitSaveClose']))
                            $this->_redirect($returnUrl);
                        else
                            $this->_redirect(str_replace($this->view->baseUrl(), '', $this->view->url(array(
                                        'action' => 'edit',
                                        $this->_paramId => $recordID,
                                        'lang' => $this->languageSuffix
                                    )))
                            );
                    }
                }
            }
        }
    }

    public function deleteAction()
    {
        $this->view->title = "Suppression d'une categorie";

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            // Get the e id
            $id = (int) $this->_getParam($this->_paramId);
            // generate the form
            $returnUrl = $this->_moduleTitle . "/"
                . $this->_name . "/"
                . $this->_defaultAction . "/";

            $this->view->assign(
                'return', $this->view->baseUrl() . "/" . $returnUrl
            );

            $oData = new BannerFeaturedObject();
            $select = $oData->getAll(null, false, $id);

            $data = $this->_db->fetchRow($select);

            $this->view->data = $data;

            if ($this->_request->isPost())
            {
                $del = $this->_request->getPost('delete');
                if ($del && $id > 0)
                {
                    // get all';
                    $oData->delete($id);
                    Cible_FunctionsGeneral::delFolder($this->_imagesFolder . $id);

                    $oImgFeat = new BannerFeaturedImageObject();
                    $oImgFeat->delAssociatedImg($id);
                }

                $this->_redirect($returnUrl);
            }
        }
    }

    public function listAction()
    {
        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $tables = array(
                'BannerFeaturedData' => array(
                    'BF_ID'),
                'BannerFeaturedIndex' => array(
                    'BFI_DataID',
                    'BFI_LanguageID',
                    'BFI_Label'
                )
            );
            $field_list = array(
                'BF_ID' => array('width' => '50px'),
                'BF_Name' => array('width' => '300px'),
            );

            $this->view->params = $this->_getAllParams();
            $pageID = $this->_getParam('pageID');
            $langId = $this->_defaultInterfaceLanguage;

            $lines = new BannerFeaturedObject();
            $select = $lines->getAll($langId, false);

            $options = array(
                'commands' => array(
                    $this->view->link($this->view->url(
                            array(
                                'controller' => $this->_name,
                                'action' => 'add'
                            )
                        ), $this->view->getCibleText('button_add_featured'), array('class' => 'action_submit add')
                    )
                ),
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
                                'replace' => 'BF_ID'
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
                                'replace' => 'BF_ID'
                            )
                        )
                    )
                )
            );

            $mylist = New Cible_Paginator($select, $tables, $field_list, $options);
            $this->view->assign('mylist', $mylist);
        }
    }

    protected function _setImage($source, $newData, $recordID)
    {
        $config = Zend_Registry::get('config')->toArray();

        if ($this->_editMode)
            $srcOriginal = $this->_imagesFolder . $recordID . "/tmp/" . $newData[$source];
        else
            $srcOriginal = $this->_imagesFolder . "tmp/" . $newData[$source];


        $originalMaxHeight = $config[$this->_moduleTitle][$this->_imgIndex]['original']['maxHeight'];
        $originalMaxWidth = $config[$this->_moduleTitle][$this->_imgIndex]['original']['maxWidth'];
        $originalName = str_replace(
            $newData[$source], $originalMaxWidth
            . 'x'
            . $originalMaxHeight
            . '_'
            . $newData[$source], $newData[$source]
        );

        $srcMedium = $this->_imagesFolder
            . "tmp/medium_"
            . $newData[$source];
        $mediumMaxHeight = $config[$this->_moduleTitle][$this->_imgIndex]['medium']['maxHeight'];
        $mediumMaxWidth = $config[$this->_moduleTitle][$this->_imgIndex]['medium']['maxWidth'];

        if ($mediumMaxHeight > 0 && $mediumMaxWidth > 0)
        {
            $mediumName = str_replace(
                $newData[$source], $mediumMaxWidth
                . 'x'
                . $mediumMaxHeight
                . '_'
                . $newData[$source], $newData[$source]
            );
        }

        $srcThumb = $this->_imagesFolder
            . "tmp/thumb_"
            . $newData[$source];
        $thumbMaxHeight = $config[$this->_moduleTitle][$this->_imgIndex]['thumb']['maxHeight'];
        $thumbMaxWidth = $config[$this->_moduleTitle][$this->_imgIndex]['thumb']['maxWidth'];
        $thumbName = str_replace(
            $newData[$source], $thumbMaxWidth
            . 'x'
            . $thumbMaxHeight
            . '_'
            . $newData[$source], $newData[$source]
        );
        if(file_exists($srcOriginal)){
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
                rename($srcMedium, $this->_imagesFolder . $recordID . "/" . $mediumName);
            }

            rename($srcOriginal, $this->_imagesFolder . $recordID . "/" . $originalName);
            rename($srcThumb, $this->_imagesFolder . $recordID . "/" . $thumbName);
        }
    }

    protected function _setImageSrc($record, $source, $recordID, $format = 'thumb')
    {
        // image src.
        $config = Zend_Registry::get('config')->toArray();
        $thumbMaxHeight = $config[$this->_moduleTitle][$this->_imgIndex]['thumb']['maxHeight'];
        $thumbMaxWidth = $config[$this->_moduleTitle][$this->_imgIndex]['thumb']['maxWidth'];
        $isNewImage = true;

        if (!empty($record[$source]))
        {
            $this->view->assign(
                'imageUrl', $this->_rootImgPath
                . $recordID . "/"
                . str_replace(
                    $record[$source], $thumbMaxWidth
                    . 'x'
                    . $thumbMaxHeight
                    . '_'
                    . $record[$source], $record[$source])
            );
            $isNewImage = false;
        }

        if ($this->_request->isPost())
        {
            $data = $this->_request->getPost();
            $formData = $data;

//            array_merge(
//                $data['productFormLeft'], $data['productFormRight'], $data['productFormBottom']);

            if ($formData[$source] <> $record[$source])
            {
                if ($formData[$source] == "")
                {
                    $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                    $isNewImage = false;
                }
                else
                {
                    $imageSrc = $this->_rootImgPath
                        . $recordID
                        . "/tmp/mcith/mcith_"
                        . $formData[$source];

                    $isNewImage = true;
                }
            }
            else
            {
                if ($record[$source] == "")
                {
                    $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                    $isNewImage = false;
                }
                else
                {
                    $imageSrc = $this->_rootImgPath
                        . $recordID . "/"
                        . str_replace(
                            $record[$source], $thumbMaxWidth
                            . 'x'
                            . $thumbMaxHeight . '_'
                            . $record[$source], $record[$source]);
                    $isNewImage = true;
                }
            }
        }
        else
        {
            if (empty($record[$source]))
                $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
            else
                $imageSrc = $this->_rootImgPath
                    . $recordID . "/"
                    . str_replace(
                        $record[$source], $thumbMaxWidth
                        . 'x'
                        . $thumbMaxHeight . '_'
                        . $record[$source], $record[$source]);
        }

        return array('imageSrc' => $imageSrc, 'isNewImage' => $isNewImage);
    }

    private function _saveImgData($formData, $recordID, $action)
    {
        $imgId = 0;
        $tmpId = 0;

        $config = Zend_Registry::get('config')->toArray();
        $useOver = $config['banners']['imagefeat']['over'];


        foreach ($formData as $key => $value)
        {

            if (preg_match('/^IF_Img/', $key))
                $imgId = preg_replace('/[a-zA-Z]*_[a-zA-Z]*/', '', $key);

            if (preg_match('/IF_Img[0-9]*$/', $key)
                    || preg_match('/IFI_[a-zA-Z]*[0-9]*$/', $key)
                    || preg_match('/IF_Style[0-9]*$/', $key)
                    || preg_match('/IF_Effect[0-9]*$/', $key)
                    || preg_match('/IF_EffectOver[0-9]*$/', $key)
                    || preg_match('/IFI_TextA[0-9]*$/', $key)
                    || preg_match('/IF_ImgOver[0-9]*$/', $key)
                    || preg_match('/IFI_TextB[0-9]*$/', $key))
            {

                $dbField = preg_replace('/[0-9]*$/', '', $key);
                $imgData[$imgId]['IF_ImgID'] =  $imgId;
                $imgData[$imgId]['IF_DataID'] =  $recordID;
                $imgData[$imgId][$dbField] =  $value;
            }

            if ($tmpId != $imgId)
                $tmpId = $imgId;
        }


        $oBannerImgFeat = new BannerFeaturedImageObject();

       if ($action == 'add')
        {
            foreach ($imgData as $imgFeat){

                $oBannerImgFeat->insert($imgFeat, $this->_defaultEditLanguage);
            }
        }

        if ($action == 'edit')
        {
            $tmpData = $oBannerImgFeat->getData($this->_defaultEditLanguage, $recordID);

            foreach ($tmpData as $data)
            {
                $index   = $data['IF_ID'];
                $imgFeat = $imgData[$data['IF_ImgID']];

                if (!empty($imgFeat))
                    $oBannerImgFeat->save($index, $imgFeat, $this->_currentEditLanguage);
            }
        }




    }
}