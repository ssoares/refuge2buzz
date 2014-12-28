<?php

class Fonctionnalites_IndexController extends Cible_Controller_Action {

    protected $_moduleID = 50;
    protected $_defaultAction = 'index';
    protected $_moduleTitle = 'fonctionnalites';
    protected $_currentAction = '';
    protected $_name = '';
    protected $_ID = 'FD_ID';
    protected $_actionKey = '';
    protected $_colTitle = array();
    protected $_imageFolder;
    protected $_rootImgPath;
    protected $_formName = '';

    /**
     * Overwrite the function define in the SiteMapInterface implement in Cible_Controller_Action
     *
     * This function return the sitemap specific for this module
     *
     * @access public
     *
     * @return a string containing xml sitemap
     */
    public function siteMapAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $bannersRob = new EquipeRobots();
        $dataXml = $bannersRob->getXMLFile($this->_registry->absolute_web_root, $this->_request->getParam('lang'));

        parent::siteMapAction($dataXml);
    }

    public function listAction() {
        $this->view->headLink()->offsetSetStylesheet($this->_moduleID, $this->view->locateFile('fonctionnalites.css'), 'all');
        $this->view->headLink()->appendStylesheet($this->view->locateFile('fonctionnalites.css'), 'all');
        $this->_name = "fonctionnalites";

        $employeeId = "";

        //$this->_rootImgPath = $this->view->BaseUrl() . "/data/images/" . $this->_name . "/";
        $this->_rootImgPath = $this->view->BaseUrl() . $this->view->currentSite . "/data/images/" . $this->_name . "/";
        $this->view->headScript()->appendFile($this->view->locateFile('jquery.cibleCollapseList.js', 'jquery'));


        $paramAction = $this->_request->getParam('action');

        $langID = Zend_Registry::get("languageID");

        $listEmploye = new FonctionnalitesObject();
        $select = $listEmploye->getAll($langID, false);
        $select->order("FDD_Seq ASC");
        $data = $this->_db->fetchAll($select);
        

        $this->view->assign("fonctionnalites", $data);
        $this->view->assign("imagePath", $this->_rootImgPath);
        $this->renderScript('index/index.phtml');
    }

    public function addAction() {
        
    }

    public function editAction() {
        
    }

    public function deleteAction() {
        
    }

    public function listallAction() {
        
    }

}

?>