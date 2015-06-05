<?php

abstract class Cible_Controller_Action extends Zend_Controller_Action implements Cible_Controller_SiteMapInterface {

    const SEPARATOR = '||';
    const IS_MOBILE = 'mobile';
    const IS_TABLET = 'tablet';

    protected $_db;
    protected $_moduleID = 0;
    protected $_isXmlHttpRequest = false;
    protected static $defaultEditLanguage = 1;
    protected $_defaultEditLanguage = 1;
    protected $_currentEditLanguage = 1;
    protected $_defaultInterfaceLanguage = 1;
    protected $_currentInterfaceLanguage = 1;
    protected $_siteType = 's';
    protected $_currentSite = '';
    protected $_config;
    protected $_registry;
    protected $type;
    protected $_showActionButton = true;
    protected $_showBlockTitle = false;
    protected $_showBlockId = 0;
    protected $_addSubFolder = false;
    protected $_imagesFolder;
    protected $_rootImgPath;
    protected $_filesFolder;
    protected $_rootFilesPath;
    protected $_moduleTitle = '';
    protected $_currentAction = '';
    protected $_isSecured = false;
    protected $_blockId = null;
    protected $_duplicateId = 0;
    protected $_defaultAdapter = null;
    protected $_duplicateData = array();
    protected $_cibleAdmin = false;
    protected $_device = null;
    protected $_deviceType = null;

    public function siteMapAction(array $dataXml = array()) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $xmlString = "";
        if (count($dataXml) > 0) {
            $xmlString = header('Content-Type: text/xml');
            $xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
            $xmlString .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

            foreach ($dataXml as $i) {
                $xmlString .= "<url><loc>";
                $xmlString .= $i[0];
                $xmlString .= '</loc><lastmod>';
                $xmlString .= $i[3];
                $xmlString .= '</lastmod><changefreq>';
                $xmlString .= $i[2];
                $xmlString .= '</changefreq><priority>';
                $xmlString .= $i[1];
                $xmlString .= '</priority></url>';
            }
            $xmlString .= '</urlset>';
        }

        echo $xmlString;
    }

    public function getRobot() {

    }

    /**
     * Set the if the block id must be displayed.
     *
     * @return void
     */
    public function setBlockId($value) {
        $this->_showBlockId = $value;
        $this->view->blockId = $value;
        $this->_blockId = $value;
    }

    /**
     * Set the if the block title must be displayed.
     *
     * @return void
     */
    public function setShowBlockTitle($value) {
        $this->_showBlockTitle = $value;
    }

    /**
     * Set the module id.
     *
     * @return int
     */
    public function setModuleId() {
        $this->_moduleID = Cible_FunctionsModules::getModuleIDByName($this->_request->getModuleName());
    }

    /**
     * Get the module id from controller.
     *
     * @return int
     */
    public function getModuleID() {
        return $this->_moduleID;
    }

    /**
     * Get the typ os site from controller.
     *
     * @return char
     */
    public function getSiteType() {
        return $this->_siteType;
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init() {
        parent::init();

        if (!defined('ISCRONJOB'))
            $this->switchProtocol();

        $this->_autoloadModulesClasses();

        $this->_registry = Zend_Registry::getInstance();

        $this->view->assign('current_module', $this->_request->getModuleName());

        if ($this->_getParam('enableDictionnary')) {
            $this->_registry->set('enableDictionnary', 'true');
            $this->view->assign('enableDictionnary', 'true');
        } else {
            $this->_registry->set('enableDictionnary', 'false');
            $this->view->assign('enableDictionnary', 'false');
        }

        $this->_db = $this->_registry->get('db');

        $this->_config = $this->_registry->get('config');

        if ($this->_config->defaultEditLanguage) {
            $this->_defaultEditLanguage = $this->_config->defaultEditLanguage;
            self::$defaultEditLanguage = $this->_config->defaultEditLanguage;
        }
        if ($this->_config->dictionnary_is_allowed == true)
            $this->_registry->set('dictionnary_is_allowed', 'true');
        else
            $this->_registry->set('dictionnary_is_allowed', 'false');

        $_request = $this->_request;

        $session = new Cible_Sessions(SESSIONNAME);
        if ($this->_request->isXmlHttpRequest()) {


            if (!empty($session->languageID))
                $this->_registry->set('languageID', $session->languageID);

            if (!empty($session->siteType))
                $this->_siteType = $session->siteType;

            $this->_isXmlHttpRequest = true;
            Zend_Registry::set('isXmlHttpRequest', $this->_isXmlHttpRequest);
            $this->disableLayout();

            if ($_request->isPost()) {
                foreach ($_request->getPost() as $key => $value)
                    $_request->setPost($key, $value);

                $this->setRequest($_request);
            }
        } else {

            $this->view->assign('params', $_request->getParams());
        }
        $siteType = $_request->getParam('site');

        if (!empty($siteType)) {
            $this->_siteType = $siteType;
            $session->siteType = $this->_siteType;
        } elseif (isset($session->siteType))
            $this->_siteType = $session->siteType;

        if (Zend_Registry::isRegistered('currentSite'))
            $this->_currentSite = Zend_Registry::get('currentSite');

        if (!preg_match('/extranet/', FRONTEND)) {
            $this->setDevice();
            $this->setDeviceType();
        }

        $islogged = Cible_FunctionsGeneral::getAuthentication();
        Zend_Registry::set('user', $islogged);
        $this->setShowBlockTitle((bool) $this->_getParam('showHeader'));
        $this->setBlockId($this->_getParam('BlockID'));

        $dataPath = "../../"
                . $this->_config->document_root
                . '/' . $session->currentSite . "/data/";

        $this->_imagesFolder = $dataPath
                . "images/"
                . $this->_moduleTitle . "/";

        $this->_rootImgPath = Zend_Registry::get("www_root")
                . $session->currentSite . "/data/images/"
                . $this->_moduleTitle . "/";
        $this->_filesFolder = $dataPath
                . "files/";

        $this->_rootFilesPath = Zend_Registry::get("www_root")
                . $session->currentSite . "/data/files/";
        if ($this->_addSubFolder) {
            $this->_imagesFolder .= $this->_currentAction . '/';
            $this->_rootImgPath .= $this->_currentAction . '/';
            $this->_filesFolder .= $this->_currentAction . '/';
            $this->_rootFilesPath .= $this->_currentAction . '/';
        }


        Zend_Registry::set('imagesFolder', $this->_imagesFolder);
        Zend_Registry::set('rootImgPath', $this->_rootImgPath);
        Zend_Registry::set('rootImgPath', $this->_rootImgPath);
        Zend_Registry::set('rootFilesPath', $this->_rootFilesPath);

        $this->view->showBlockTitle = $this->_showBlockTitle;
        $this->view->assign('request', $_request);
    }

    public static function getDefaultEditLanguage() {
        return self::$defaultEditLanguage;
    }

    public static function getRobotString() {
        return self::getRobot();
    }

    public function getCurrentInterfaceLanguage() {
        return $this->_currentInterfaceLanguage;
    }

    public function getCurrentEditLanguage() {
        return $this->_currentEditLanguage;
    }

    /*     * *
     * disableLayout enables you to disable layout wihtout having to remember how to do it with the layout helper
     *
     */

    protected function disableLayout() {
        $this->_helper->layout->disableLayout();
    }

    /*     * *
     * disableView enables you to disable the viewrenderer wihtout having to remember how to do it with the viewrenderer helper
     *
     */

    protected function disableView() {
        $this->_helper->viewRenderer->setNoRender();
    }

    protected function retrieveActions() {

        $class = new ReflectionClass($this);
        $methods = array();

        foreach ($class->getMethods() as $_method) {
            $_name = $_method->getName();

            if ($_method->isProtected() || $_method->isPublic()) {
                if (strlen($_name) > 6 && substr($_name, -6) == 'Action') {
                    array_push($methods, $_name);
                }
            }
        }

        return $methods;
    }

    protected function retrieveProperties() {

        $class = new ReflectionClass($this);
        $properties = array();

        foreach ($class->getProperties() as $_property) {
            $_name = $_property->getName();

            if (strlen($_name) > 6 && substr($_name, -6) == 'Action') {
                array_push($properties, $_name);
            }
        }

        return $properties;
    }

    public function toExcelAction() {
        if (empty($this->filename))
            throw new Exception('You must define $this->filename for the output filename');

        if (empty($this->select))
            throw new Exception('You must define $this->select a select statement');

        if (empty($this->fields))
            throw new Exception('You must define $this->fields as an array with all the fields');


        $this->disableLayout();
        $this->disableView();

        if ($this->select) {

            if ($this->filters) {
                $filters = $this->filters;

                foreach ($filters as $key => $filter) {
                    $filter_val = $this->_getParam($key);
                    if (!empty($filter_val))
                        $this->select->where("{$filter['associatedTo']} = ?", $filter_val);
                }
            }

            if ($this->_getParam('order')) {

                if (in_array($this->_getParam('order'), array_keys($this->fields))) {

                    $direction = 'ASC';
                    if (in_array($this->_getParam('order-direction'), array('ASC', 'DESC')))
                        $direction = $this->_getParam('order-direction');

                    $this->select->order("{$this->_getParam('order')} {$direction}");
                }
            }

            $searchfor = $this->_getParam('searchfor');

            if ($searchfor) {

                $searching_on = array();
                $search_keywords = explode(' ', $searchfor);

                foreach ($this->tables as $table => $columns) {
                    foreach ($columns as $column) {
                        array_push($searching_on, $this->_db->quoteInto("{$table}.{$column} LIKE ?", "%{$searchfor}%"));
                        foreach ($search_keywords as $keyword)
                            array_push($searching_on, $this->_db->quoteInto("{$table}.{$column} LIKE ?", "%{$keyword}%"));
                    }
                }

                if (!empty($searching_on))
                    $this->select->where(implode(' OR ', $searching_on));
            }

            $results = $this->_db->fetchAll($this->select);

            $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $cacheSettings = array('memoryCacheSize ' => '8MB');
            if (!PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings))
                die($cacheMethod . " caching method is not available" . EOL);

            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $column = 0;
            foreach ($this->fields as $field_name => $field_value) {
                if (is_array($field_value))
                    $label = !empty($field_value['label']) ? $field_value['label'] : $this->view->getCibleText("list_column_{$field_name}");
                else
                    $label = $field_value;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $label);
                $column++;
            }

            $key = 2;
            foreach ($results as $value) {

                foreach (array_keys($this->fields) as $i => $field_value) {
                    if (!empty($value[$field_value])) {
                        $val = $value[$field_value];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $key, $val);
                    }
                }
                $key++;
            }

            // load the appropriate IO Factory writer
            switch ($this->type) {
                case 'Excel5':
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $this->type);
                    // output the appropriate headers
                    header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                    header("Content-Disposition: attachment;filename={$this->filename}");
                    break;

                case 'CSV':
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $this->type);
                    $objWriter->setDelimiter(';');
                    $objWriter->setLineEnding("\r\n");
                    $objWriter->setUseBOM(true);
                    header("Content-type: application/vnd.ms-excel");
                    header("Content-Disposition: attachment;filename={$this->filename}");
                    break;

                default:
                    if (empty($this->type))
                        $this->type = 'Excel2007';
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $this->type);
                    // output the appropriate headers
                    header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                    header("Content-Disposition: attachment;filename={$this->filename}");
                    break;
            }

            // output the file
            $objWriter->save('php://output');

            exit;
        }
    }

    public function toPdfAction() {
        if (empty($this->filename))
            throw new Exception('You must define $this->filename for the output filename');

        if (empty($this->select))
            throw new Exception('You must define $this->select a select statement');

        if (empty($this->fields))
            throw new Exception('You must define $this->fields as an array with all the fields');


        $this->disableLayout();
        $this->disableView();

        if ($this->select) {

            if ($this->filters) {
                $filters = $this->filters;

                foreach ($filters as $key => $filter) {
                    $filter_val = $this->_getParam($key);
                    if (!empty($filter_val))
                        $this->select->where("{$filter['associatedTo']} = ?", $filter_val);
                }
            }

            if ($this->_getParam('order')) {

                if (in_array($this->_getParam('order'), $this->fields)) {

                    $direction = 'ASC';
                    if (in_array($this->_getParam('order-direction'), array('ASC', 'DESC')))
                        $direction = $this->_getParam('order-direction');

                    $this->select->order("{$this->_getParam('order')} {$direction}");
                }
            }

            $searchfor = $this->_getParam('searchfor');

            if ($searchfor) {

                $searching_on = array();
                $search_keywords = explode(' ', $searchfor);

                foreach ($this->tables as $table => $columns) {
                    foreach ($columns as $column) {
                        array_push($searching_on, $this->_db->quoteInto("{$table}.{$column} LIKE ?", "%{$searchfor}%"));
                        foreach ($search_keywords as $keyword)
                            array_push($searching_on, $this->_db->quoteInto("{$table}.{$column} LIKE ?", "%{$keyword}%"));
                    }
                }

                if (!empty($searching_on))
                    $this->select->where(implode(' OR ', $searching_on));
            }

            $results = $this->_db->fetchAll($this->select);

            $objPHPExcel = new PHPExcel();

            $objPHPExcel->setActiveSheetIndex(0);


            foreach (array_keys($this->fields) as $i => $field_value) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, $field_value);
            }

            $key = 2;
            foreach ($results as $value) {

                foreach (array_keys($this->fields) as $i => $field_value) {
                    if (!empty($value[$field_value]))
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $key, $value[$field_value]);
                }
                $key++;
            }

            // load the appropriate IO Factory writer
            $objWriter = new PHPExcel_Writer_PDF($objPHPExcel);
            // output the appropriate headers
            header("Content-type: application/pdf");
            header("Content-Disposition: attachment;filename={$this->filename}");

            // output the file
            $objWriter->save('php://output');

            exit;
        }
    }

    /**
     * Create an csv file for data export using PHPExcel library.
     *
     * @return void
     */
    public function toCsvAction() {
        if (empty($this->filename))
            throw new Exception('You must define $this->filename for the output filename');

        if (empty($this->select))
            throw new Exception('You must define $this->select a select statement');

        if (empty($this->fields))
            throw new Exception('You must define $this->fields as an array with all the fields');


        $this->disableLayout();
        $this->disableView();

        if ($this->select) {

            if ($this->filters) {
                $filters = $this->filters;

                foreach ($filters as $key => $filter) {
                    $filter_val = $this->_getParam($key);
                    if (!empty($filter_val))
                        $this->select->where("{$filter['associatedTo']} = ?", $filter_val);
                }
            }

            if ($this->_getParam('order')) {

                if (in_array($this->_getParam('order'), array_keys($this->fields))) {

                    $direction = 'ASC';
                    if (in_array($this->_getParam('order-direction'), array('ASC', 'DESC')))
                        $direction = $this->_getParam('order-direction');

                    $this->select->order("{$this->_getParam('order')} {$direction}");
                }
            }

            $searchfor = $this->_getParam('searchfor');

            if ($searchfor) {

                $searching_on = array();
                $search_keywords = explode(' ', $searchfor);

                foreach ($this->tables as $table => $columns) {
                    foreach ($columns as $column) {
                        array_push($searching_on, $this->_db->quoteInto("{$table}.{$column} LIKE ?", "%{$searchfor}%"));
                        foreach ($search_keywords as $keyword)
                            array_push($searching_on, $this->_db->quoteInto("{$table}.{$column} LIKE ?", "%{$keyword}%"));
                    }
                }

                if (!empty($searching_on))
                    $this->select->where(implode(' OR ', $searching_on));
            }

            $results = $this->_db->fetchAll($this->select);

            $objPHPExcel = new PHPExcel();

            $objPHPExcel->setActiveSheetIndex(0);

            $column = 0;
            $key = 1;
            // Insert columns label, if needed set $key = 2
            if ($this->addColumnsLabel) {
                foreach ($this->fields as $field_name => $field_value) {

                    if (is_array($field_value)) {
                        $label = !empty($field_value['label']) ? $field_value['label'] : $this->view->getCibleText("list_column_{$field_name}");
                    } else {
                        $label = trim($field_value);
                    }

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $label);
                    $column++;
                }
                $key = 2;
            }
            foreach ($results as $value) {

                foreach (array_keys($this->fields) as $i => $field_value) {
                    if (isset($value[$field_value]))
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $key, trim($value[$field_value]));
                }
                $key++;
            }

            // load the appropriate IO Factory writer
            switch ($this->type) {
                case 'Excel5':
                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    // output the appropriate headers
                    header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                    header("Content-Disposition: attachment;filename={$this->filename}");
                    // output the file
                    $objWriter->save('php://output');
                    break;

                case 'CSV':
                    $objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
                    $objWriter->setDelimiter(';');
                    $objWriter->setLineEnding("\r\n");
                    // Save file on the server
                    if ($this->_exportFilesFolder)
                        $objWriter->save($this->_exportFilesFolder . $this->filename);
                    else
                        $objWriter->save('php://output');

                    break;

                default:
                    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                    // output the appropriate headers
                    header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                    header("Content-Disposition: attachment;filename={$this->filename}");
                    // output the file
                    $objWriter->save('php://output');
                    break;
            }
        }
    }

    public function ajaxCitiesAction() {
        if ($this->_isXmlHttpRequest) {
            $this->getHelper('viewRenderer')->setNoRender();

            $stateId = $this->_getParam('stateId');
            $cities = new CitiesObject();
            $citiesData = $cities->getCitiesDataByStates($stateId);

            foreach ($citiesData as $id => $data) {
                $citiesData[$id]['C_Name'] = $data['C_Name'];
            }

            echo json_encode($citiesData);
        }
    }

    public function ajaxStatesAction() {
        if ($this->_isXmlHttpRequest) {
            $this->getHelper('viewRenderer')->setNoRender();

            $countryId = $this->_getParam('countryId');
            $languageId = $this->_getParam('langId');
            $statesData = array();
            $states = Cible_FunctionsGeneral::getStateByCode(
                            $countryId, null, $languageId);

            if (is_array($states)) {
                foreach ($states as $id => $data) {
                    $statesData[$id]['id'] = $data['id'];
                    $statesData[$id]['name'] = $data['name'];
                }
            }

            echo json_encode($statesData);
        }
    }

    public function ajaxAction() {
        $action = $this->_getParam('actionAjax');
        $this->disableView();
        $value = $this->_getParam('term');
        $state = $this->_getParam('state');
        $limit = $this->_getParam('limit');
        $list = array();

        switch ($action) {
            case 'citiesList':
                $oCity = new CitiesObject();

                if (!empty($value))
                    $data = $oCity->autocompleteSearch(
                            $value, $this->getCurrentInterfaceLanguage(), $limit, $state
                    );

                foreach ($data as $value)
                    array_push($list, $value['C_Name']);

                echo json_encode($list);
                break;
            case 'lostPassword':
                $email = $this->_getParam('email');

                if (empty($email)) {
                    echo json_encode(array('result' => 'fail', 'message' => 'email missing'));
                    return;
                }

                $profile = new MemberProfile();
                $user = $profile->findMember(array('email' => $email));
                if (!Zend_Registry::isRegistered('languageSuffix')) {
                    $langs = Cible_FunctionsGeneral::getLanguageSuffix($user['language']);
                    Zend_Registry::set('languageSuffix', $langs);
                }
                if ($user) {

                    $password = Cible_FunctionsGeneral::generatePassword();
                    $profile->updateMember($user['member_id'], array('password' => md5($password), 'hash' => ''));

                    // send the mail
                    $data = array(
                        'email' => $email,
                        'PASSWORD' => $password,
                        'language' => $user['language'],
                    );
                    $options = array(
                        'send' => true,
                        'isHtml' => true,
                        'moduleId' => $this->_moduleID,
                        'event' => 'newPassword',
                        'type' => 'email',
                        'recipient' => 'client',
                        'data' => $data
                    );

                    new Cible_Notifications_Email($options);
                } else {
                    echo json_encode(array('result' => 'fail', 'message' => $this->view->getClientText('lost_password_email_not_found')));
                    return;
                }

                echo json_encode(array('result' => 'success', 'message' => $this->view->getClientText('lost_password_sent')));
                break;
            case 'locationName':
                $oObj = new StudiesObject();
                $field = 'SI_LocationName';
                if (!empty($value))
                    $data = $oObj->autocompleteSearch(
                            $value, $this->_currentEditLanguage, $limit, $field
                    );

                foreach ($data as $value)
                    array_push($list, $value[$field]);
                echo json_encode($list);
                break;
            case 'locationAddr':
                $oObj = new StudiesObject();
                $field = 'SI_LocationAddr';
                if (!empty($value))
                    $data = $oObj->autocompleteSearch(
                            $value, $this->_currentEditLanguage, $limit, $field
                    );

                foreach ($data as $value)
                    array_push($list, $value[$field]);

                echo json_encode($list);
                break;
            case 'setEnv':
                $session = new Zend_Session_Namespace(SESSIONNAME);
                $session->currentSite = $value;
                $this->setAcl();
                echo json_encode($session->currentSite);
                break;
            case 'pageSiteSrc':
                Zend_Registry::set('pageSiteSrc', $value);
                break;
            case 'clearCache':
                $session = new Zend_Session_Namespace(SESSIONNAME);
                $session->parameters = array();
                break;
            case 'findLink':
                $oBlocks = new BlocksObject();
                $hasLinks = (bool) $oBlocks->setProperties(array('pageId' => $value))->linksExist();
                echo json_encode($hasLinks);
                break;
            default:
                break;
        }
    }

    public function setAcl(Zend_Auth $auth = null, Zend_Auth_Adapter_DbTable $adapter = null, $login = false) {
        $session = new Zend_Session_Namespace(SESSIONNAME);
        $dbs = Zend_Registry::get('dbs');
        if (is_null($auth))
            $auth = Zend_Auth::getInstance();
        if ($login) {
            if (is_null($adapter))
                $adapter = new Zend_Auth_Adapter_DbTable($this->_db, 'Extranet_Users', 'EU_Username', 'EU_Password', 'MD5(?)');

            $user = array(
                'EU_ID',
                'EU_LName',
                'EU_FName',
                'EU_Email',
                'EU_DefaultSite',
                'EU_SiteAccess',
                'EU_ShowError'
            );
            $userData = $adapter->getResultRowObject($user);
            $userData->isAdminCible = $this->_cibleAdmin;
            $auth->getStorage()->write($userData);

            // build ACL rights
            $data = (array) $auth->getStorage()->read();
            if (!empty($data['EU_DefaultSite']))
                $session->currentSite = $data['EU_DefaultSite'];
            $userId = $data['EU_ID'];
        }
        elseif (!empty($session->currentSite) && !is_null($auth->getStorage()->read())) {
            $dbAdapter = $dbs->getDb($session->currentSite);
            Zend_Db_Table::setDefaultAdapter($dbAdapter);
            Zend_Registry::set('db', $dbAdapter);
            $userId = $auth->getStorage()->read()->EU_ID;
        }
        if (!empty($userId)) {
            $session->acl = Cible_FunctionsAdministrators::getACLUser($userId);
        }
    }

    /**
     * Fecth modules using data indexation and load class to allow
     * Cible_FunctionsIndexation::indexationBuild() to load data.
     *
     * @return void
     */
    private function _autoloadModulesClasses() {
        $modules = Cible_FunctionsModules::getModules(true);

        foreach ($modules as $module) {
            $className = $module['M_Indexation'];
            Zend_Loader::autoload($className);
        }
    }

    public function setAlertMsg($alertMsg) {
        $this->_alertMsg = $this->view->AlertMessage($alertMsg);
        $this->view->placeholder('alert')->set($this->_alertMsg);
    }

    public function cropimageAction() {
        $this->disableLayout();
        $this->disableView();

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();

            $mylist = New Cible_FunctionsCrop(array(), $formData);
            $mylist->cropImage();
            if ($this->_isXmlHttpRequest) {
                echo 'success';
            } else {
                $this->_redirect($formData['returnPage']);
            }
        } else {
            $arrays = $this->_request->getParams();
            $varA = array('fileSource' => $this->_imageSource, 'fileDestination' => $this->_imageSource,
                'returnPage' => $this->_returnAfterCrop,
                'cancelPage' => $this->_cancelPage,
                'submitPage' => $this->view->baseUrl() . "/" . $arrays['module'] . "/" . $arrays['controller'] . "/" . $arrays['action'],
                'sizeYWanted' => $this->_headerHeight, 'sizeXWanted' => $this->_headerWidth,
                'showActionButton' => $this->_showActionButton);

            $mylist = New Cible_FunctionsCrop($varA, "");
            $mylist->cropRenderImage();
        }
    }

    public function loginAction() {
        $this->setModuleId();
        $this->view->headLink()->offsetSetStylesheet($this->_moduleID, $this->view->locateFile('profile.css'), 'all');
        $this->view->headLink()->appendStylesheet($this->view->locateFile('profile.css'), 'all');
        $account = Cible_FunctionsGeneral::getAuthentication();

        if (is_null($account)) {
            $path = Zend_Registry::get('web_root');
            setcookie('returnUrl', $this->view->selectedPage, 0, $path);
            $message = $this->_getParam('message');
            if (!empty($message))
                $this->view->assign('message', $message);

            $form = new FormLogin();
            $noSubmitOther = true;
            if (isset($_POST['submit']))
                $noSubmitOther = false;
            elseif (isset($_POST['unsubscribe']))
                $noSubmitOther = false;
            elseif (isset($_POST['subscribe']))
                $noSubmitOther = false;
            elseif (isset($_POST['submitFind']))
                $noSubmitOther = false;

            if ($this->_request->isPost() && $noSubmitOther) {
                $formData = $this->_request->getPost();

                if ($form->isValid($formData)) {
                    $result = Cible_FunctionsGeneral::authenticate($formData['emailLogin'], $formData['password']);

                    if ($result['success'] == 'true' && empty($result['validatedEmail']) && $result['status'] == 2) {
                        $this->disableView();
                        $hash = md5(session_id());
                        $duration = $formData['stayOn'] ? time() + (60 * 60 * 24 * 30) : 0;
//                        $duration = $formData['stayOn'] ? time() + (60 * 60 * 24 * 2) : 0;
                        $cookie = array(
                            'lastName' => $result['lastName'],
                            'firstName' => $result['firstName'],
                            'email' => $result['email'],
                            'language' => $result['language'],
                            'hash' => $hash,
                            'status' => $result['status']
                        );

                        setcookie("authentication", json_encode($cookie), $duration, $path);

                        $memberProfile = new MemberProfile();
                        $memberProfile->updateMember($result['member_id'], array('hash' => $hash));

                        if ($this->_registry->isRegistered('pageID')) {
                            $pageId = $this->_registry->get('pageID');
                            $redirectUrl = $this->_request->getPathInfo();
                            if ($cookie['language'] != Zend_Registry::get('languageID')) {
                                $redirectUrl = Cible_FunctionsPages::getPageNameByID($pageId, $cookie['language']);
                                Zend_Registry::set('languageID', $cookie['language']);
                            }
                        }

//                        $redirectUrl = Cible_View_Helper_LastVisited::getLastVisited(1);
                        if (empty($redirectUrl)) {
                            $homePage = Cible_FunctionsPages::getHomePageDetails();
                            $redirectUrl = $homePage['PI_PageIndex'];
                        }

                        $this->_redirect($redirectUrl);
                    } else {
                        if ($result['success'] == 'true') {
                            if (!empty($result['validatedEmail']) || $result['status'] < 2) {
                                setcookie("authentication");
                                $url = Cible_FunctionsCategories::getPagePerCategoryView(0, 'confirm_email', $this->_moduleID);
                                $url .= '/email/' . $formData['emailLogin'];
                                $this->_redirect($url);
                            }
                        } else {
                            $error = Cible_Translation::getClientText('login_form_auth_fail_error');
                            $this->view->assign('error', $error);
                        }
                    }
                }
            }

            $this->view->assign('form', $form);
        } else {

            if (Zend_Registry::get('pageID') == $this->_config->authentication->pageId) {
                $this->_redirect(Cible_FunctionsCategories::getPagePerCategoryView(0, 'become_client', $this->_moduleID));
            }
            $this->disableView();
        }
    }

    public function captchaReloadAction() {
        $captcha_image = $this->view->formAddCaptcha(null, array('getCaptcha' => true));
        $image = $captcha_image->generate();
        $captcha['id'] = $captcha_image->getId();
        $captcha['word'] = $captcha_image->getWord();
        $captcha['url'] = $captcha_image->getImgUrl() . $image . $captcha_image->getSuffix();

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        echo Zend_Json::encode($captcha);
    }

    private function _testDataForNotification(array $formData, $memberData) {
        $isModified = array();

        foreach ($formData as $key => $value) {
            if (!isset($memberData[$key]) && $key == 'duplicate')
                $isModified[$key] = $value;

            if (is_array($value))
                $isModified[$key] = $this->_testDataForNotification($value, $memberData[$key]);
            elseif (array_key_exists($key, $memberData) && !preg_match('/^password/', $key) && ($value != $memberData[$key])) {
                $isModified[$key] = $value;
            }
        }

        return $isModified;
    }

    public function modifyclientAction() {
        $this->becomeclientAction();
        $this->renderScript('index/becomeclient.phtml');
    }

    /**
     * Create a new account or edit an existing one.
     *
     * @return void
     */
    public function becomeclientAction() {
        $this->setModuleId();
        $this->view->headLink()->offsetSetStylesheet(28, $this->view->locateFile('profile.css'), 'all');
        $this->view->headLink()->appendStylesheet($this->view->locateFile('profile.css'), 'all');
        // Test if the user is already connected.
        $account = Zend_Registry::get('user');
        // Set the default status to an account creation and not editing one
        $_edit = false;
        // Instantiate the user profiles
        $profile = new MemberProfilesObject();
        $profile->setOGeneric();
//        $newsletterProfile = new NewsletterProfile();
        $memberData = array();
        $accountValidate = true;
        //Set Default id for states and cities
        $config = Zend_Registry::get('config');
        $current_state = $config->address->default->states;
        $addr = array();
        $currentCity = '';
        $addPage = Cible_FunctionsCategories::getPagePerCategoryView(1, 'become_client', $this->_moduleID, null, true);
        $editPage = Cible_FunctionsCategories::getPagePerCategoryView(0, 'modifyclient', $this->_moduleID);
        // Get users data if he is already logged
        if ($account) {
            if ($account['status'] == 2 || $this->_request->isPost()) {
                $_edit = true;
                $memberData = $profile->findData(array('email' => $account['email']));
//                $newsletterData = $newsletterProfile->findMember(array('email' => $account['email']));
                if (!empty($memberData['address'])) {
                    $current_state = $memberData['address']['A_StateId'] . self::SEPARATOR;
                    $currentCity = $memberData['address']['A_CityId'] . self::SEPARATOR;
                }
                Zend_Registry::set('modifTitle', $this->view->getClientText('account_modify_page_title'));
                if (preg_match('/' . $this->view->selectedPage . '/', $addPage))
                    $this->_redirect($editPage);

//                $this->view->headTitle($this->view->getClientText('account_modify_page_title'));
//                $this->view->pageTitle = $this->view->getClientText('account_modify_page_title');
            }
        }

        $this->view->assign('accountValidate', $accountValidate);

        $options = $_edit ? array('mode' => 'edit') : array();
        $_edit ? $this->view->assign('mode', 'edit') : $this->view->assign('mode', 'add');
        // Instantiate the form for account management
        $form = new FormBecomeClient($options);
        //$_captcha = $form->getElement('captcha');
        $this->view->assign('selectedCity', $currentCity);
        $this->view->assign('selectedState', $current_state);

        // Test if the users has ticked the aggreement checkbox
        $agreementError = isset($_POST['termsAgreement']) && $_POST['termsAgreement'] != 1 ? true : false;

        if ($_edit){
            $agreementError = false;
        }
        $this->view->assign('agreementError', $agreementError);
        // Actions when form is submitted
        if ($this->_request->isPost() && array_key_exists('submit', $_POST)) {
            $formData = $this->_request->getPost();
            $findEmail = $profile->findMember(array('email' => $formData['identification']['email']));
//            $nlData = $newsletterProfile->findMember(array('email' => $formData['identification']['email']));
            // Test if the email already exists and password is the same
            if ($_edit) {
                $subFormI = $form->getSubForm('identification');
                if ($formData['identification']['email'] <> $memberData['email']) {
                    $findEmail = $profile->findMember(array('email' => $formData['identification']['email']));

                    if ($findEmail) {
                        $emailNotFoundInDBValidator = new Zend_Validate_Db_NoRecordExists('GenericProfiles', 'GP_Email');
                        $emailNotFoundInDBValidator->setMessage($this->view->getClientText('validation_message_email_already_exists'), 'recordFound');
                        $subFormI->getElement('email')->addValidator($emailNotFoundInDBValidator);
                    }
                }

                if (empty($formData['identification']['password']) && empty($formData['identification']['passwordConfirmation'])) {
                    $subFormI->getElement('password')->clearValidators()->setRequired(false);
                    $subFormI->getElement('passwordConfirmation')->clearValidators()->setRequired(false);
                }
            }
//            elseif (!$_edit && !$findEmail && $nlData)
            elseif (!$_edit && !$findEmail) {
                $subFormI = $form->getSubForm('identification');
                $subFormI->getElement('email')->removeValidator('Zend_Validate_Db_NoRecordExists');
//                $hasNewsletterProfile = true;
            }

            $oAddress = new AddressObject();
            // Get the addresses data to insert
            if ($form->isValid($formData) && !$agreementError) {
                $oNotification = new Cible_Notifications_Email();

                if (!empty($formData['password'])) {
                    $password = $formData['password'];
                    $formData['password'] = md5($password);
                }

                if (!$_edit) {
                    // Do the processing here
                    $validatedEmail = Cible_FunctionsGeneral::generatePassword();

                    $path = Zend_Registry::get('web_root') . '/';
                    $hash = md5(session_id());
                    $duration = 0;
                    $cookie = array(
                        'lastName' => $formData['lastName'],
                        'firstName' => $formData['firstName'],
                        'email' => $formData['email'],
                        'hash' => $hash,
                        'status' => 2
                    );

                    $formData['hash'] = $hash;
                    $formData['validatedEmail'] = $validatedEmail;
                    $formData['validatedEmail'] = '';
                    $formData['status'] = 0;

                    //Add addresses process and retrive id for memberProfiles
                    $idMember = $profile->addProfile($formData);
                    $memberData = $profile->populate($idMember, 1);
                    $cookie['member_id'] = $idMember;

                    setcookie("authentication", json_encode($cookie), $duration, $path);

                    $dataAdm = array(
                        'firstname' => $memberData['firstName'],
                        'lastname' => $memberData['lastName'],
                        'email' => $memberData['email'],
                        'language' => $memberData['language'],
                        'NEWID' => $idMember
                    );
                    $optionsAdm = array(
                        'send' => true,
                        'isHtml' => true,
                        'moduleId' => $this->_moduleID,
                        'event' => 'newAccount',
                        'type' => 'email',
                        'recipient' => 'admin',
                        'data' => $dataAdm
                    );

                    $oNotification->process($optionsAdm);
                    $oNotification = new Cible_Notifications_Email();
                    $data = array(
                        'firstName' => $memberData['firstName'],
                        'lastName' => $memberData['lastName'],
                        'email' => $memberData['email'],
                        'language' => $formData['language'],
                        'validatedEmail' => '',
                        'password' => $password,
                    );
                    $options = array(
                        'send' => true,
                        'isHtml' => true,
                        'to' => $formData['email'],
                        'moduleId' => $this->_moduleID,
                        'event' => 'newAccount',
                        'type' => 'email',
                        'recipient' => 'client',
                        'data' => $data
                    );

                    $oNotification->process($options);
                    Cible_FunctionsGeneral::authenticate($formData['email'], $password);
                    $logData = array(
                        'L_ModuleID' => $this->_moduleID,
                        'L_UserID' => $idMember,
                        'L_Action' => 'newAccount',
                        'L_Data' => '',
                    );
                    $oLog = new Cible_Log(array('userId' => $idMember, 'user' => $cookie));
                    $oLog->log(array('data' => $logData));

                    $redirectUrl = Cible_View_Helper_LastVisited::getLastVisited(2);
                    if (!empty($redirectUrl))
                        $this->_redirect($redirectUrl);
                    else
                        $this->renderScript('index/become-client-thank-you.phtml');
                }
                else {
                    if (empty($formData['password']))
                        unset($formData['password']);
                    $oAddress->save($addr['A_AddressId'], $address, $formData['language']);
                    $profile->updateMember($memberData['member_id'], $formData);

                    if ($formData['email'] <> $memberData['email']) {
//                        $validatedEmail = Cible_FunctionsGeneral::generatePassword();
                        $formData['validatedEmail'] = '';
                        $profile->updateMember($memberData['member_id'], $formData);
//
//                        $confirm_page = Zend_Registry::get('absolute_web_root') . "/"
//                            . Cible_FunctionsCategories::getPagePerCategoryView(
//                                0, 'confirm_email', $this->_moduleID, $formData['language'])
//                            . "/email/{$formData['email']}/validateNumber/$validatedEmail";
//
//
//                        $this->view->assign('needConfirm', true);
//                        $this->renderScript('index/confirm-email.phtml');
                        $data = array(
                            'firstName' => $memberData['firstName'],
                            'lastName' => $memberData['lastName'],
                            'email' => $memberData['email'],
                            'language' => $formData['language'],
                            'validatedEmail' => '',
                            'password' => $password,
                        );
                        $options = array(
                            'send' => true,
                            'isHtml' => true,
                            'to' => $formData['email'],
                            'moduleId' => $this->_moduleID,
                            'event' => 'editResend',
                            'type' => 'email',
                            'recipient' => 'client',
                            'data' => $data
                        );

                        $oNotification->process($options);
                    } else {

                        $authentication = json_decode($_COOKIE['authentication'], true);
                        $path = Zend_Registry::get('web_root') . '/';
                        $duration = 0;
                        $cookie = array(
                            'lastName' => $formData['lastName'],
                            'firstName' => $formData['firstName'],
                            'email' => $authentication['email'],
                            'language' => $formData['language'],
                            'hash' => $authentication['hash'],
                            'status' => 2
                        );
                        setcookie("authentication", json_encode($cookie), $duration, $path);

                        $this->view->assign('messages', array($this->view->getCibleText('form_account_modified_message')));
                        $this->view->assign('updatedName', $formData['firstName']);
                    }
//                    $data = array(
//                            'identification' => $memberData,
//                            'address'=> $addr);
//                    $notifyAdmin = $this->_testDataForNotification(
//                        $this->_request->getPost(),
//                        $data);
//                    $message = $this->view->getClientText('account_modified_admin_notification_message', $formData['language']);
//                    $titleAdmin = $this->view->getClientText('account_modified_admin_notification_title', $formData['language']);
                    $form->populate($_POST);
                    $this->view->assign('form', $form);
                    // Notify admin
//                    if (count($notifyAdmin) > 0)
//                    {
//                        $states = Cible_FunctionsGeneral::getStatesByCountry($address['A_CountryId']);
//                        foreach ($states as $value)
//                            $tmpStates[$value['ID']] = $value['Name'];
//
//                        $view = $this->getHelper('ViewRenderer')->view;
//                        $view->assign('data', $notifyAdmin);
//                        $view->assign('states', $tmpStates);
//                        $changesList = $view->render('index/changesList.phtml');
//
//                        $this->_emailRenderData['emailHeader'] = $this->view->clientImage('logo.jpg', null, true);
//
//                        $message = str_replace('##firstname##', $memberData['firstName'], $message);
//                        $message = str_replace('##lastname##', $memberData['lastName'], $message);
//                        $message = str_replace('##email##', $memberData['email'], $message);
//                        $message = str_replace('##NEWID##', $memberData['member_id'], $message);
//                        $message = str_replace('##TABLE##', $changesList, $message);
//
//                        $this->_emailRenderData['message'] = $message;
//                        $this->_emailRenderData['footer'] = $this->view->getClientText("email_notification_footer", $formData['language']);
//                        $this->view->assign('emailRenderData', $this->_emailRenderData);
//                        $html = $view->render('index/emailNotification.phtml');
//
//                        $notify = new Cible_Notify(array(
//                                'isHtml' => '1',
//                                'to' => $this->_config->notification->accountCreation->sender,
//                                'from' => $this->_config->notification->accountCreation->sender,
//                                'title' => $titleAdmin,
//                                'message' => $html
//                            ));
//                        $notify->send();
//                    }
                }
            } else{
                $form->populate($formData);
            }
        }elseif ($_edit && empty($this->view->step)){
            $form->populate($memberData);
        }
        $this->view->assign('form', $form);
    }

    public function thankYouAction() {
        $return = $this->_getParam('return');
        if ($return)
            $this->view->assign('return', $return);
    }

    public function switchProtocol() {
        $session = new Zend_Session_Namespace(SESSIONNAME);
        if ($this->_isSecured) {
            $url = 'https://' . $_SERVER['SERVER_NAME'] . $this->_request->getRequestUri();
            if (!isset($_SERVER['HTTPS'])) {
                $session->securedPage = $this->view->currentPageID;
                $this->_redirect($url);
            }
        } elseif (!$this->_isSecured) {
            $url = 'http://' . $_SERVER['SERVER_NAME'] . $this->_request->getRequestUri();
            if (isset($_SERVER['HTTPS'])) {
                if (!is_null($this->view->currentPageID) && $this->view->currentPageID != $session->securedPage) {
                    $session->securedPage = null;
                    $this->_redirect($url);
                }
            }
        }
    }

    protected function _testDuplicatedContent($testImgPath = false) {
        $this->_duplicateData = (array) Cible_FunctionsBlocks::getDuplicateData($this->_blockId, $this->view->languageId);

        if (!empty($this->_duplicateData)) {
            $dbs = Zend_Registry::get('dbs');
            $this->_defaultAdapter = $dbs->getDefaultDb();
            $this->_db = $dbs->getDb($this->_duplicateData['B_FromSite']);
            $this->_duplicateId = $this->_duplicateData['B_DuplicateId'];
            $imgPath = Zend_Registry::get('rootImgPath');
            if ($testImgPath) {
                $imgNewPath = preg_replace('~(.*)' . preg_quote($this->view->currentSite, '~') . '~', '$1' . $this->_duplicateData['B_FromSite'], $imgPath, 1);

                Zend_Registry::set('rootImgPath', $imgNewPath);
            }
        }
    }

    protected function _resetDefaultAdapter() {
        if (!is_null($this->_defaultAdapter))
            $this->_db = $this->_defaultAdapter;
    }

    public function setDevice() {
        $wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
        $wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfigFile);
        $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
        $wurflManager = $wurflManagerFactory->create();
        $this->_device = $wurflManager->getDeviceForHttpRequest(filter_input_array(INPUT_SERVER));
    }

    public function setDeviceType() {
        $type = $this->_device->getCapability('device_os');
        $forceStandard = filter_input(INPUT_COOKIE, 'displayStandard');
        Zend_Registry::set('isMobile', false);
        //Zend_Registry::set('isMobile', true);
        if ($forceStandard != session_id()) {
            switch ($type) {
                case ''; //pour ie 11
                case 'Desktop':
                    $this->_deviceType = null;
                    break;
                default:
                    $this->_deviceType = self::IS_MOBILE;
                    if ($this->_device->getCapability('is_tablet') == 'true') {
                        $this->_deviceType = self::IS_TABLET;
                    } else
                        Zend_Registry::set('isMobile', true);
                    break;
            }
        }else {
            setcookie('displayStandard', null, null);
        }
    }

    public function postDispatch() {
        if (Zend_Registry::isRegistered('isMobile'))
            $mobile = Zend_Registry::get('isMobile');
        else
            $mobile = false;

        if ($mobile) {
            $tplView = self::IS_MOBILE . '-' . $this->_request->getActionName();
            $paths = $this->view->getScriptPaths();
            $script = $paths[0] . $this->_request->getControllerName() . '/';
            if (file_exists($script . $tplView . '.phtml')) {
                $this->render($tplView);
            }
        }
    }

}
