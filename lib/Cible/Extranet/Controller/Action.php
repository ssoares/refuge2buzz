<?php

abstract class Cible_Extranet_Controller_Action extends Cible_Controller_Action
{
    protected $_mobileManagement = false;
    protected $_hasProfile = false;
    protected $_hasVideos = false;
    protected $_hasReindexation = false;

    protected $_headerWidth = "";
    protected $_headerHeight = "";
    protected $_imageSource = "";
    protected $_returnAfterCrop = "";
    protected $_cancelPage = "";
    protected $_moduleTitle = '';
    protected $_showActionButton = true;
    protected $_imgIndex = 'image';
    protected $_imagesFolder;
    protected $_rootImgPath;
    protected $_filesFolder;
    protected $_rootFilesPath;
    protected $_editMode = false;
    protected $_cleanup = true;
    protected $_grayScale = false;
    protected $_dataIdField = '';

    public function init()
    {
        parent::init();

        $arrays = $this->_request->getParams();
        $this->view->assign('user', $this->view->auth());
//        if ($this->_isXmlHttpRequest && empty($this->view->user))
//            $this->_redirect($this->view->url());

        $session = new Cible_Sessions(SESSIONNAME);

        if (isset($this->_config->embedVideos))
            $this->_hasVideos = (bool) $this->_config->embedVideos;

        if (isset($this->_config->hasReindexation))
        {
            $this->_hasReindexation = (bool) $this->_config->hasReindexation;
            $this->view->hasReindexation = $this->_hasReindexation;
        }

        if(isset($this->_config->mobileManagement))
            $this->_mobileManagement = (bool) $this->_config->mobileManagement;

        $config = Zend_Registry::get('config')->toArray();

        if(!empty($config['image']['background']['show']))
            $this->_hasBackgroundImages = (bool) $config['image']['background']['show'];
        if(!empty($config['image']['entete']['show']))
            $this->_hasHeaderImages = (bool)$config['image']['entete']['show'];
        if(!empty($config['image']['background']['size']['height']))
            $this->_imageHeaderHeight = (int)$config['image']['entete']['size']['height'];
        if(!empty($config['image']['background']['size']['width']))
            $this->_imageHeaderWidth = (int)$config['image']['entete']['size']['width'];

        $okToList = false;
        if (isset($arrays['action']))
        {
            if (strlen($arrays['action']) > 3)
            {
                //echo substr($arrays['action'], 0, 4);
                if (substr($arrays['action'], 0, 4) == 'list')
                    $okToList = true;
            }
        }

        if ((isset($arrays['controller'])) && (isset($arrays['action'])))
        {
            if (($arrays['controller'] == 'static-texts') && ($arrays['action'] == 'index'))
                $okToList = true;

            $perPageSession = "perPage_" . $arrays['action'] . "_" . $arrays['controller'];
            $pageSession = "page_" . $arrays['action'] . "_" . $arrays['controller'];
        }
        if (isset($arrays['action']))
        {
            if(($arrays['action'] == 'list-all-images')||($arrays['action'] =='list-images'))
                $okToList = false;
        }

        if ($okToList == true && !$this->_isXmlHttpRequest)
        {
            $reload = false;

            $urlInfo = $this->_request->getPathInfo();
            $urlInfoT = strrev($urlInfo);
            if ($urlInfoT[0] != "/")
                $urlInfo .= "/";

            if (!empty($session->$perPageSession))
            {
                if ($this->_getParam('perPage'))
                {
                    $perPageVar = $this->_getParam('perPage');
                    if ($session->$perPageSession != $perPageVar)
                    {
                        $session->$perPageSession = $perPageVar;
                        $replaceP = "/perPage/" . $session->$perPageSession;
                        $oldPageP = "/\/perPage\/[0-9]*/";
                        $urlInfo = preg_replace($oldPageP, $urlInfo, $replaceP);
                        $reload = true;
                    }
                }
                else
                {
                    $urlInfoTmp = strrev($urlInfo);
                    if ($urlInfoTmp[0] == "/")
                        $urlInfo = $urlInfo . "perPage/" . $session->$perPageSession . "/";
                    else
                        $urlInfo = $urlInfo . "/perPage/" . $session->$perPageSession . "/";

                    $reload = true;
                }
            }
            else
                $session->$perPageSession = 10;

            if (!empty($session->$pageSession))
            {
                if ($this->_getParam('page'))
                {
                    $pageVar = $this->_getParam('page');
                    if ($session->$pageSession != $pageVar)
                    {
                        $session->$pageSession = $pageVar;
                        $replaceP = "/page/" . $session->$pageSession;
                        $oldPageP = "/\/page\/[0-9]*/";
                        $urlInfo = preg_replace($oldPageP, $urlInfo, $replaceP);
                        $reload = true;
                    }
                }
                else
                {
                    $urlInfoTmp = strrev($urlInfo);
                    if ($urlInfoTmp[0] == "/")
                        $urlInfo = $urlInfo . "page/" . $session->$pageSession . "/";
                    else
                        $urlInfo = $urlInfo . "/page/" . $session->$pageSession . "/";

                    $reload = true;
                }
            }
            else
                $session->$pageSession = 1;

            if ($reload == true)
                $this->_redirect($urlInfo);
        }

        // Defines the default interface language
        if ($this->_config->defaultInterfaceLanguage)
            $this->_defaultInterfaceLanguage = $this->_config->defaultInterfaceLanguage;

        // Check if the current interface language should be different than the default one
        $this->_currentInterfaceLanguage = !empty($session->languageID) ? $session->languageID : $this->_defaultInterfaceLanguage;

        if ($this->_getParam('setLang'))
            $this->_currentInterfaceLanguage = Cible_FunctionsGeneral::getLanguageID($this->_getParam('setLang'));

        // Registers the current interface language for future uses
        $this->_registry->set('languageID', $this->_currentInterfaceLanguage);
        $session->languageID = $this->_currentInterfaceLanguage;

        $suffix = Cible_FunctionsGeneral::getLanguageSuffix($this->_currentInterfaceLanguage);
        $this->_registry->set('languageSuffix', $suffix);

        // Defines the default edit language
        if ($this->_config->defaultEditLanguage)
            $this->_currentEditLanguage = $this->_config->defaultEditLanguage;
        else
            $this->_currentEditLanguage = $this->_defaultEditLanguage;

        $this->_currentEditLanguage = !empty($session->currentEditLanguage) ? $session->currentEditLanguage : $this->_currentEditLanguage;

        // Check if the current edit language should be different than the default one
        if ($this->_getParam('lang'))
        {
            $this->_currentEditLanguage = Cible_FunctionsGeneral::getLanguageID($this->_getParam('lang'));
        }

        // Registers the current edit language for future uses
        $this->_registry->set('currentEditLanguage', $this->_currentEditLanguage);
        $session->currentEditLanguage = $this->_currentEditLanguage;

        if (Cible_FunctionsGeneral::extranetLanguageIsAvailable($this->getCurrentInterfaceLanguage()) == 0)
        {

            $session = new Cible_Sessions(SESSIONNAME);

            $this->_currentInterfaceLanguage = $this->_config->defaultInterfaceLanguage;

            // Registers the current interface language for future uses
            $this->_registry->set('languageID', $this->_currentInterfaceLanguage);
            $session->languageID = $this->_currentInterfaceLanguage;

            $suffix = Cible_FunctionsGeneral::getLanguageSuffix($this->_currentInterfaceLanguage);
            $this->_registry->set('languageSuffix', $suffix);
        }

        $modProfile = Cible_FunctionsModules::modulesProfile();

        if (count($modProfile) > 0)
            $this->_hasProfile = true;

        $this->setAcl();
        $this->view->assign('hasProfile', $this->_hasProfile);
        $this->view->assign('hasVideos', $this->_hasVideos);
        $this->view->assign('mobileManagement', $this->_mobileManagement);
    }

    public function cropimageAction(){
        $this->disableView();

        if ($this->_request->isPost())
        {
            $formData = $this->_request->getPost();
            $mylist = New Cible_FunctionsCrop(array(),$formData);
            $mylist->cropImage();

            if($this->_isXmlHttpRequest)
               echo 'success';
            else
                $this->_redirect($formData['returnPage']);
        }
        else
        {

            $arrays = $this->_request->getParams();
            $varA = array('fileSource'=> $this->_imageSource, 'fileDestination'=> $this->_imageSource,
                'returnPage'=> $this->_returnAfterCrop,
                'cancelPage'=> $this->_cancelPage,
                'submitPage'=> $this->view->baseUrl() . "/" . $arrays['module'] . "/" . $arrays['controller'] . "/" . $arrays['action'],
                'sizeYWanted'=>$this->_headerHeight,'sizeXWanted'=>$this->_headerWidth,
                'showActionButton'=> $this->_showActionButton);

            $mylist = New Cible_FunctionsCrop($varA, "");
            $mylist->cropRenderImage();
        }
    }

    /**
     * Test if the image is a new one and return path and status flag.
     *
     * @param array $record Data form database.
     * @param string $source The field to get filename.
     * @param int $recordID The id of the current record.
     * @param string $format The size format to fetch parameter from config file.
     *
     * @return array
     */
    protected function _setImageSrc($record, $source, $recordID, $format = 'thumb')
    {
        // image src.
        $config = Zend_Registry::get('config')->toArray();
        $thumbMaxHeight = $config[$this->_moduleTitle][$this->_imgIndex][$format]['maxHeight'];
        $thumbMaxWidth = $config[$this->_moduleTitle][$this->_imgIndex][$format]['maxWidth'];
        $isNewImage = true;
        $imgBasePath = $this->_rootImgPath . $recordID . "/";
        $nameSize = $thumbMaxWidth . 'x' . $thumbMaxHeight . '_';

        if (!empty($record[$source]))
        {
            $this->view->assign('imageUrl', $imgBasePath
                . str_replace(
                    $record[$source],
                    $nameSize . $record[$source],
                    $record[$source])
                );
            $isNewImage = false;
        }

        if ($this->_request->isPost())
        {
            $formData = $this->_request->getPost();

//            array_merge(
//                $data['productFormLeft'], $data['productFormRight'], $data['productFormBottom']);
            $postedImg = $this->_getPostedImg($formData, $source, $recordID);
            if ($postedImg['isset'] && (empty($record[$source]) || $postedImg['value'] <> $record[$source]))
            {
                if ($formData[$source] == "")
                    $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                else
                    $imageSrc = $imgBasePath
                        . "/tmp/mcith/mcith_"
                        . $formData[$source];

                $isNewImage = true;
            }
            else
            {
                if ($record[$source] == "")
                    $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                else
                    $imageSrc = $imgBasePath
                        . str_replace(
                            $record[$source],
                            $nameSize . $record[$source], $record[$source]);

                    $isNewImage = false;
            }
        }
        else
        {
            if (!empty($recordID))
                if (!is_dir($this->_imagesFolder . $recordID))
                {
                    mkdir($this->_imagesFolder . $recordID)
                        or die("Could not make directory");
                    mkdir($this->_imagesFolder . $recordID . "/tmp")
                        or die("Could not make directory");
                }

            if (empty($record[$source]))
                $imageSrc = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
            else
                $imageSrc = $this->_rootImgPath
                    . $recordID . "/"
                    . str_replace(
                        $record[$source],
                        $nameSize. $record[$source],
                        $record[$source]);
        }

        return array('imageSrc' => $imageSrc, 'imgBasePath' => $imgBasePath, 'nameSize' => $nameSize, 'isNewImage' => $isNewImage);
    }

    protected function _getPostedImg($formData, $source, $recordID)
    {
        $isset = false;
        $id = 0;
        foreach ($formData as $key => $value)
        {
            if (is_array($value))
            {
                $tmp = $this->_getPostedImg ($value, $source, $recordID);
                $isset = $tmp['isset'];
                $data = $tmp['value'];
                $id = $tmp['id'];
            }
            else
            {
                if (isset($formData[$source]))
                {
                    if (!empty($formData[$this->_dataIdField]))
                        $id = $formData[$this->_dataIdField];

                    $data = $formData[$source];
                    $isset = true;
                    break;
                }
                else
                    $isset = false;

            }

            if ($id == $recordID)
                break;
        }

        if ($isset)
            $return = array('isset' => true, 'value' => $data, 'id' => $id);
        else
            $return = array('isset' => false, 'value' => null);

        return $return;
    }

    /**
     * Resizes and saves images file into folder according to modules parameters.
     *
     * @param string $source The key to get filename from data array.
     * @param array $newData The data sent by form.
     * @param int $recordID  The id of the current record.
     *
     * @return void
     */
    protected function _setImage($source, $newData, $recordID)
    {
        $config = Zend_Registry::get('config')->toArray();
        $dimensions = $config[$this->_moduleTitle][$this->_imgIndex];

        if ($this->_editMode)
        {
            $srcImg = $this->_imagesFolder . $recordID . "/tmp/";
            if ($this->_cleanup)
                $this->_cleanupFolder($this->_imagesFolder . $recordID . '/');
        }
        else
            $srcImg = $this->_imagesFolder . "tmp/";

        foreach ($dimensions as $size => $dims)
        {
            $tmpSrc = $srcImg . "{$size}_" . $newData[$source];
            copy($srcImg . $newData[$source], $tmpSrc);

            $maxWidth = $dims['maxWidth'];
            $maxHeight = $dims['maxHeight'];

            $name = str_replace(
                $newData[$source], $maxWidth
                . 'x'
                . $maxHeight
                . '_'
                . $newData[$source], $newData[$source]
            );
            $options = array(
                'src' => $tmpSrc,
                'maxWidth' => $maxWidth,
                'maxHeight' => $maxHeight);
            if (isset($dims['forceWidth']))
                $options['forceWidth'] = $dims['forceWidth'];
            if (isset($dims['forceHeight']))
                $options['forceHeight'] = $dims['forceHeight'];

            Cible_FunctionsImageResampler::resampled($options);



            if($this->_grayScale==true){


                copy($tmpSrc, $this->_imagesFolder . $recordID . "/" . $name);
                $options['grayScale']=true;
                Cible_FunctionsImageResampler::resampled($options);
                $name = str_replace(
                $newData[$source], $maxWidth
                . 'x'
                . $maxHeight
                . '_gray_'
                . $newData[$source], $newData[$source]
            );
                //var_dump($tmpSrc, $this->_imagesFolder . $recordID . "/" . $name);
                //exit;
            rename($tmpSrc, $this->_imagesFolder . $recordID . "/" . $name);
            }
            else
                rename($tmpSrc, $this->_imagesFolder . $recordID . "/" . $name);

            if (file_exists($tmpSrc))
                unlink($tmpSrc);
        }
        if (file_exists($srcImg . $newData[$source]))
        {
            unlink($srcImg . $newData[$source]);
        }

    }

    /**
     * Delete files from folder
     *
     * @param type $currentFolder
     */
    protected function _cleanupFolder($currentFolder)
    {
        $dirHandler = opendir($currentFolder);
        // for each file in the folder
        while (($file = readdir($dirHandler)) !== false)
        {
            $realPath = realpath($currentFolder . $file);
            if (filetype($realPath) == 'file')
                unlink($realPath);
        }
    }
}
