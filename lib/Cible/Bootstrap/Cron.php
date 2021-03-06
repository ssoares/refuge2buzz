<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cron
 *
 * @author soaser
 */
class Bootstrap_Cron extends Zend_Application_Bootstrap_Bootstrap
{
    const NAME = 'multidb';
    /**
     * String to define if we are int the front office
     */
    const FO_NAME = '/application/';
    /**
     * String to define if we are int the back office
     */
    const BO_NAME = '/extranet/';

    protected $_extranetPath = '';
    protected $_applicationPath = '';
    protected $_currentSite = '';
    protected $_moduleDirectory = 'modules';

    public function run()
    {
        try
        {
            if ($this->hasPluginResource('cron'))
            {
                $this->bootstrap('cron');
                $server = $this->getResource('cron');
                $server->run();
            }
            else
            {
                echo 'The cron plugin resource needs to be configured in application.ini.' . PHP_EOL;
            }
        }
        catch (Exception $e)
        {
            echo 'An error has occured.' . PHP_EOL;
            Zend_Debug::dump($e);
        }
    }

    /**
     * Initialize Module
     *
     * @return Zend_Application_Module_Autoloader
     */
    protected function _initAutoload()
    {
        /**
         * Path to the front-office folders.
         */
        $this->_applicationPath = APPLICATION_PATH . self::FO_NAME;
        /**
         * Path to the back-office folders.
         */
        $this->_appPath = FRONTEND;

        if (preg_match(self::FO_NAME, FRONTEND))
            define('SESSIONNAME', str_replace('/', '', self::FO_NAME));
        elseif(preg_match(self::BO_NAME, FRONTEND))
            define('SESSIONNAME', str_replace('/', '', self::BO_NAME));
    }

    protected function _initSession()
    {
        $session = new Zend_Session_Namespace(SESSIONNAME);

        return $session;
    }

    protected function _initConfig()
    {
        $aen = str_replace(array("-dev","-staging"), array("",""),APPLICATION_ENV);
        $imgCfg = new Zend_Config_Ini($this->_applicationPath . "config.ini", array($aen ,'Images'), true);
        $cfg = new Zend_Config_Ini($this->_appPath . "config.ini", 'general', true);
        $config = new Zend_Config($this->getOptions(), true);
        $config->merge($imgCfg);
        $config->merge($cfg);
        $config->readOnly();

        Zend_Registry::set('config', $config);

        return $config;
    }

    /**
     * Add databases to the registry
     *
     * @return Zend_Db_Adapter_Pdo_Mysql
     */
    public function _initDbRegistry()
    {
        $this->bootstrap('multidb');
        $multidb = $this->getPluginResource('multidb');
        Zend_Registry::set('dbs', $multidb);
        $dbAdapter = $multidb->getDefaultDb();

        if (isset($this->session->currentSite) && !preg_match(self::FO_NAME, FRONTEND))
            $dbAdapter = $multidb->getDb($this->session->currentSite);
        else
        {
            foreach ($this->config->multisite as $data)
            {
                if ((bool) $data->active)
                {
                    $name =  $data->name;
                    $dbName = $this->config->resources->multidb->$name->dbname;
                    $currentDbName = $dbAdapter->fetchOne("select DATABASE();");
                    if ($dbName === $currentDbName)
                        $this->session->currentSite = $data->name;

                }
            }
        }
        Zend_Db_Table::setDefaultAdapter($dbAdapter);
        Zend_Registry::set('db', $dbAdapter);

        return $dbAdapter;
    }

    protected function _initRequest()
    {
        // Vérifie que le controleur frontal est bien présent, et le récupére
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $front->addModuleDirectory($this->_appPath . $this->_moduleDirectory)
            ->throwExceptions(false);

    }

    protected function _initCache()
    {
        $frontendOptions = array(
               'lifetime' => 0,
               'automatic_serialization' => false
            );

        // Directory where to put the cache files
        if (!is_dir(APPLICATION_PATH . '/tmp/' . $this->session->currentSite))
            mkdir (APPLICATION_PATH . '/tmp/' . $this->session->currentSite);
        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/tmp/' . $this->session->currentSite
        );
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

        Zend_Registry::set('cache', $cache);
    }

    protected function _initHelper()
    {
        $lib_path = APPLICATION_PATH . "/lib";

        $view = new Zend_View();
        $view->addHelperPath("Cible/View/Helper", "Cible_View_Helper");
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        $view->addBasePath("{$lib_path}/Cible/View");
        $view->addBasePath("{$lib_path}/ZendX/JQuery/View");
        $view->addBasePath("{$lib_path}/Cible/Validate");

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    }

    protected function _initPaths()
    {
        $www_root = rtrim(dirname(dirname($_SERVER['PHP_SELF'])),'/') . "/";
        $tmpPath = "";

        $isSandbox = preg_match('/sandboxes/', $this->config->domainName);

        if (($isSandbox)&&(!preg_match('/www/', $www_root)))
        {
            $tmpPath = $this->config->document_root . "/";
            $www_root .= $tmpPath;
        }
        $protocol = 'http://';
        if(isset($_SERVER['HTTPS']))
            $protocol = 'https://';

        $absolute_web_root = $protocol . $this->config->domainName . $tmpPath;
        if (preg_match(self::FO_NAME, FRONTEND))
        {
//            $this->_currentSite = $this->view->siteList(array('getValues' => true, 'frontOffice'=> true));
        }
        else
        {
            if (!empty($this->session->currentSite))
                $this->_currentSite = $this->session->currentSite;
        }
        $lucenePath = APPLICATION_PATH . "/{$this->config->document_root}/" . $this->_currentSite . '/indexation/all_index';
        $lucenePathPdf = APPLICATION_PATH . "/{$this->config->document_root}/" . $this->_currentSite . '/indexation/pdf';

        if(!is_dir(APPLICATION_PATH . "/{$this->config->document_root}/" . $this->_currentSite)){
            mkdir(APPLICATION_PATH . "/{$this->config->document_root}/" . $this->_currentSite);
        }
        if (!is_dir(APPLICATION_PATH . "/{$this->config->document_root}/" . $this->_currentSite . '/indexation/'))
        {
            mkdir(APPLICATION_PATH . "/{$this->config->document_root}/" . $this->_currentSite . '/indexation/');
            mkdir($lucenePath);
        }
        if (!is_dir($lucenePathPdf))
            mkdir($lucenePathPdf);

        Zend_Registry::set('lucene_index', $lucenePath);
        Zend_Registry::set('lucene_pdf', $lucenePathPdf);
        Zend_Registry::set('document_root', $www_root);
        Zend_Registry::set('web_root', $www_root);
        Zend_Registry::set('www_root', $www_root);
        Zend_Registry::set('absolute_web_root', $absolute_web_root);
        $lang = $this->config->defaultInterfaceLanguage;
        Zend_Registry::set('siteName', $this->config->site->title->$lang);

    }

    /**
     * Initialize Modules path for commons modules and according to the current DB.
     *
     * @return void
     */
    protected function _initModulesPath()
    {
        $path = array();
        $modules = Cible_FunctionsModules::getModules();

        set_include_path('.' . $this->_appPath . "$this->_moduleDirectory"
            . PATH_SEPARATOR . $this->_appPath . "$this->_moduleDirectory/default/models/"
            . PATH_SEPARATOR . $this->_appPath . "$this->_moduleDirectory/messages/models/"
            . PATH_SEPARATOR . $this->_appPath . "$this->_moduleDirectory/page/models/"
            . PATH_SEPARATOR . $this->_appPath . "$this->_moduleDirectory/search/models/"
            . PATH_SEPARATOR . $this->_appPath . "$this->_moduleDirectory/video/models/"
            . PATH_SEPARATOR . get_include_path());

        foreach ($modules as $module)
            $path[] = $this->_appPath . "$this->_moduleDirectory/" . $module['M_MVCModuleTitle'] . "/models/";

        set_include_path(get_include_path()
            . PATH_SEPARATOR . implode(PATH_SEPARATOR, $path));

        array_push($modules, array('M_MVCModuleTitle' => 'messages'));
        array_push($modules, array('M_MVCModuleTitle' => 'page'));
        array_push($modules, array('M_MVCModuleTitle' => 'menu'));
        array_push($modules, array('M_MVCModuleTitle' => 'video'));

        if (preg_match(self::BO_NAME, FRONTEND))
            foreach ($this->config->multisite as $params)
            {
                if ((bool)$params->firstRun)
                {
                    $sitePath = APPLICATION_PATH . "/{$this->config->document_root}/" . $params->name;
                    if (!is_dir($sitePath))
                        mkdir ($sitePath);

                    Cible_FunctionsModules::firstRun($modules, $sitePath);
                }

            }

    }
}