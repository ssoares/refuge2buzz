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
    protected $_imagesFolder;
    protected $_rootImgPath;
    protected $_filesFolder;
    protected $_rootFilesPath;
    protected $_editMode = false;
    protected $_cleanup = true;
    protected $_grayScale = false;
    protected $_tables = array();

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
