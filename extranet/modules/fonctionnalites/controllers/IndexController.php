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
 * @version   $Id: IndexController.php 1379 2013-12-29 15:40:55Z ssoares $
 *
 */

/**
 * Manage actions for catalog.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 */
class Fonctionnalites_IndexController extends Cible_Controller_Block_Abstract
{
    protected $_labelSuffix;
    protected $_colTitle      = array();
    protected $_moduleID      = 50;
    protected $_defaultAction = '';
    protected $_defaultRender = 'list';
    protected $_moduleTitle   = 'fonctionnalites';
    protected $_name          = 'index';
    protected $_ID            = 'ID';
    protected $_currentAction = '';
    protected $_actionKey     = '';
    protected $_imageSrc      = 'FD_Image';
    protected $_imageFolder;
    protected $_rootImgPath;
    protected $_formName   = '';
    protected $_joinTables = array();
    protected $_objectList = array(
        'fonctionnalites' => 'FonctionnalitesObject'
    );
    protected $_actionsList = array();

    protected $_disableExportToExcel = false;
    protected $_disableExportToPDF   = false;
    protected $_disableExportToCSV   = false;
    protected $_enablePrint          = false;
    protected $_constraint;
    protected $_filterData = array();
    protected $_editMode = false;
    protected $_session;
    protected $_renderPartial = '';
    protected $_addSubFolder = false;
    protected $_whereClause;
    protected $_associationIds = array();
    protected $_getActionParams = false;
    
    protected $_imagesLst = array();
    

    /**
     * Set some properties to redirect and process actions.
     *
     * @access public
     *
     * @return void
     */
    public function init()
    {
     
        // Sets the called action name. This will be dispatched to the method
        $this->_currentAction = $this->_getParam('action');

        parent::init();
        // The action (process) to do for the selected object
        $this->_actionKey = $this->_getParam('actionKey');

        $this->_formatName();
        $this->view->assign('cleaction', $this->_labelSuffix);

        $this->view->locale = Cible_FunctionsGeneral::getLanguageSuffix($this->_defaultEditLanguage);
    }

    public function fonctionnalitesAction($getParams = false){       
       
        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {    
      
            $this->view->headLink()->appendStylesheet($this->view->locateFile('references.css'), 'all');
            $this->view->headScript()->appendFile($this->view->locateFile('manageRefValues.js', null, 'back'));
            
            $this->_moduleTitle = $this->_moduleTitle;
            $this->_colTitle = array(
                'FD_ID'    => array('width' => '150px'),
                'FI_Title' => array('width' => '150px', 'useFormLabel' => true),
                'FI_SubTitle' => array('width' => '150px', 'useFormLabel' => true)
                );
            
           

            if ($this->_isXmlHttpRequest)
            {
                $this->ajaxAction ();
                exit;
            }

            if($getParams)
            {
                $params = array(
                    'columns'    => $this->_colTitle,
                    'joinTables' => $this->_joinTables);
                return $params;
            }
            $this->_formName = 'FormFonctionnalites';

            $this->_redirectAction();
        }
    }

    
    /**
     * Add action for the current object.
     *
     * @access public
     *
     * @return void
     */
    public function addAction(){
       
        $returnAction = $this->_getParam('return');
        $baseDir = $this->view->baseUrl() . "/";
        $oDataName = $this->_objectList[$this->_currentAction];

        $oData = new $oDataName();
        $this->_registry->currentEditLanguage = $this->_registry->languageID;
        $cancelUrl = $this->view->url(array(
            'action' => $this->_currentAction,
            'actionKey' => null,
            $this->_ID => null
        ));
        $imageSrc = '';
        $isNewImage = false;
        if ($returnAction)
            $returnUrl = $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $returnAction;
        else
            $returnUrl = str_replace($this->view->baseUrl(), '', $this->view->url(array(
                'action' => $this->_currentAction,
                'actionKey' => null,
                $this->_ID => null
            )));

        
      
        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {

            
            
            
            // generate the form
            $options = array(
                'moduleName' => $this->_moduleTitle,
                'moduleID' => $this->_moduleID,
                'baseDir' => $baseDir,
                'cancelUrl' => $cancelUrl,
                'addAction' => true,
                'imageSrc' => $imageSrc,
                'imgField' => $this->_imageSrc,
                'imgBasePath' => $imgBasePath,
                'nameSize' => $nameSize,
                'dataId'     => '',
                'object'     => $oData,
                'id' => $this->_currentAction,
                'isNewImage' => $isNewImage
            );
            
           

            $form = new $this->_formName($options);
            $this->view->form = $form;

            
            if ($this->_request->isPost())
            {
                $moreImages = array();
                $formData = $this->_request->getPost();
                
               
                if ($form->isValid($formData)){
                                        
                    
                    if($formData['DI_Url']!=""){
                        $tmpArray = explode("/",$formData['DI_Url']);
                        $urlFile = end($tmpArray);
                        $formData['DI_Url'] = $urlFile;
                    } 

                    $recordID = $oData->insert($formData, $this->_defaultEditLanguage);                    
                     
                    
                    
                
                    
                    
                    if (!is_dir($this->_imagesFolder . $recordID)){
                        mkdir($this->_imagesFolder . $recordID)
                            or die("Could not make directory");
                        mkdir($this->_imagesFolder . $recordID . "/tmp")
                            or die("Could not make directory");
                    }                    
                    
                  
                    if (!empty($this->_imageSrc)){
                        if (!empty($formData[$this->_imageSrc])){
                            //$this->_imgIndex = $this->_imageSrc;       
                          
                            $this->_setImage($this->_imageSrc, $formData, $recordID);
                            
                           
                            
                        }                        
                    }
                            
                    
                    
                    if (isset($formData['submitSaveClose']))
                        $this->_redirect($returnUrl);
                    else
                        $this->_redirect(str_replace($this->view->baseUrl(), '', $this->view->url(array(
                            'actionKey' => 'edit',
                            $this->_ID => $recordID))));
                }                
            }
        }
    }

    /**
     * Edit action for the current object.
     *
     * @access public
     *
     * @return void
     */
    public function editAction()
    {
        
        $imageSrc = "";
        $isNewImage = false;
        $id = (int) $this->_getParam($this->_ID);
        $page = (int) $this->_getParam('page');

        $baseDir = $this->view->baseUrl() . "/";
        $returnAction = $this->_getParam('return');
        $cancelUrl = $this->view->url(array(
                    'action' => $this->_currentAction,
                    'actionKey' => null,
                    $this->_ID => null
                ));

        if ($returnAction)
            $returnUrl = $this->_moduleTitle . "/"
                    . $this->_name . "/"
                    . $returnAction;
        else
            $returnUrl = str_replace($this->view->baseUrl(), '', $this->view->url(array(
                'action' => $this->_currentAction,
                'actionKey' => null,
                $this->_ID => null
                )));

        $oDataName = $this->_objectList[$this->_currentAction];

        if ($this->view->aclIsAllowed($this->_moduleTitle, 'edit', true))
        {
            $this->_editMode = true;
            $this->_cleanup = false;
            $oData = new $oDataName();
            // Get data details
            $data = $oData->populate($id, $this->_currentEditLanguage);
            
            $config = Zend_Registry::get('config')->toArray();
            
            $thumbMaxHeightBanner = $config[$this->_moduleTitle]['image']['thumb']['maxHeight'];
            $thumbMaxWidthBanner = $config[$this->_moduleTitle]['image']['thumb']['maxWidth'];
            $thumbMaxHeightSquare = $config[$this->_moduleTitle]['imageNewsletter']['thumb']['maxHeight'];
            $thumbMaxWidthSquare = $config[$this->_moduleTitle]['imageNewsletter']['thumb']['maxWidth'];
              
            
            if ($this->_request->isPost())
            {
                $formData = $this->_request->getPost();
                
                if ($formData['FD_Image'] <> $data['FD_Image'])
                {
                    if ($formData['FD_Image'] == "")
                        $post_image = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                    else
                        $post_image = $this->_rootImgPath
                                . $id
                                . "/tmp/mcith/mcith_"
                                . $formData['FD_Image'];

                    $isNewImage = 'true';
                }
                else
                {
                    if ($data['FD_Image'] == "")
                        $post_image = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                    else
                        $post_image = $this->_rootImgPath
                                . $id . "/"
                                . str_replace(
                                        $data['FD_Image' . $i],
                                        $thumbMaxWidthBanner
                                        . 'x'
                                        . $thumbMaxHeightBanner . '_'
                                        . $data['FD_Image'],
                                        $data['FD_Image']);
                }              
                
                             
            }
            else
            {
                if (empty($data['FD_Image']))
                    $post_image = $this->view->baseUrl() . "/icons/image_non_ disponible.jpg";
                else
                    $post_image = $this->_rootImgPath
                            . $id . "/"
                            . str_replace(
                                    $data['FD_Image'],
                                    $thumbMaxWidthBanner
                                    . 'x'
                                    . $thumbMaxHeightBanner . '_'
                                    . $data['FD_Image'],
                                    $data['FD_Image']);
                
                
                //var_dump($post_image);
                //sandboxes.ciblesolutions.com/fr/donna/www/donnamicros/data/images/fonctionnalites/31
                
            } 
            // generate the form
            $options = array(
                'moduleName' => $this->_moduleTitle,
                'moduleID' => $this->_moduleID,
                'baseDir' => $baseDir,
                'cancelUrl' => $cancelUrl,
                'imageSrc' => $post_image,
                'FD_Image'   => $post_image,                
                'imgField' => $this->_imageSrc,
                'imgBasePath' => $imgBasePath,
                'nameSize' => $nameSize,
                'object'   => $oData,
                'dataId'   => $this->_setId ? $data[$oData->getForeignKey()]:$id,
                'id' => $this->_currentAction,
                'isNewImage' => $isNewImage
            );
                        
            $form = new $this->_formName($options);
            $this->view->form = $form;
            $moreImages = array();

            // action
            if (!$this->_request->isPost())
            {                   
                $form->populate($data);
            }
            else
            {
                $formData = $this->_request->getPost();
                
                if ($form->isValid($formData)){                    
                        
                    /*if (!empty($this->_imagesLst)){   
                        foreach ($this->_imagesLst as $src){
                            if (!empty($formData[$src])){
                                $this->_imgIndex = $src;                                
                                $this->_setImage($this->_imageSrc, $formData, $id);                                
                            }
                        }
                    }      */
                    if (!empty($this->_imageSrc)){
                        if (!empty($formData[$this->_imageSrc])){
                            $this->_setImage($this->_imageSrc, $formData, $id);
                        }                        
                    }
                    
                    
                    $formData['AI_Url'] = Cible_FunctionsGeneral::formatValueForUrl($formData['AI_Name']);                                       
                    $oData->save($id, $formData, $this->getCurrentEditLanguage());

                    if (!isset($formData['submitSaveClose']))
                    {
                        $returnUrl = str_replace($this->view->baseUrl(), '', $this->view->url(array(
                            'actionKey' => 'edit',
                            $this->_ID => $id
                        )));
                    }

                    $this->_redirect($returnUrl);
                }                
            }
        }
    }

    /**
     * Delete action for the current object.
     *
     * @access public
     *
     * @return void
     */
    public function deleteAction()
    {
        // variables
        $page = (int) $this->_getParam('page');
        $blockId = (int) $this->_getParam('blockID');
        $id = (int) $this->_getParam($this->_ID);

        $returnUrl = str_replace($this->view->baseUrl(), '', $this->view->url(array(
                        'action' => $this->_currentAction,
                        'actionKey' => null,
                        $this->_ID => null
                    )));

        $this->view->assign(
                'return',
                $this->view->baseUrl() . "/" . $returnUrl
        );

        $this->view->action = $this->_currentAction;

        $returnAction = $this->_getParam('return');

        $oDataName = $this->_objectList[$this->_currentAction];
        $oData = new $oDataName();

        if (Cible_ACL::hasAccess($page))
        {
            if ($this->_request->isPost())
            {
                $del = $this->_request->getPost('delete');
                if ($del && $id > 0)
                {
                    $tmp = $oData->delete($id);
                    Cible_FunctionsGeneral::delFolder($this->_imagesFolder . $id);
                    if (!empty($this->_deleteFolder))
                        Cible_FunctionsGeneral::delFolder($this->_deleteFolder . $id);

                    $this->_redirect($returnUrl);
                }

                $this->_redirect($returnUrl);
            }
            elseif ($id > 0)
            {
                $this->view->data = $oData->populate($id, $this->getCurrentEditLanguage());
            }
        }
    }

    /**
     * Creates the list of data for this action for the current object.
     *
     * @access public
     *
     * @param string $objectName String tot create the good object.
     *
     * @return void
     */
    private function _listAction($objectName)
    {
        $page = $this->_getParam('page');

        if ($page == '')
            $page = 1;
        // Create the object from parameter
        $oData = new $objectName();

        // get needed data to create the list
        $columnData  = $oData->getDataColumns();
        $dataTable   = $oData->getDataTableName();
        $indexTable  = $oData->getIndexTableName();
        $columnIndex = $oData->getIndexColumns();
        $tabId = $oData->getDataId();
        //Set the tables from previous collected data
        $tables = array(
            $dataTable => $columnData,
            $indexTable => $columnIndex
        );
        // Set the select query to create the paginator and the list.
        $select = $oData->getAll($this->_defaultEditLanguage, false);

        $params = array('foreignKey' => $oData->getForeignKey());

        /* If needs to add some data from other table, tests the joinTables
         * property. If not empty add tables and join clauses.
         */
        $select = $this->_addJoinQuery($select, $params);

        // Set the the header of the list (columns name used to display the list)
        $field_list = $this->_colTitle;

        // Set the options of the list = links for actions (add, edit, delete...)
        $options = $this->_setActionsList($tabId, $page);

        $mylist = New Cible_Paginator($select, $tables, $field_list, $options);
        // Assign a the view for rendering
        $this->_helper->viewRenderer->setRender($this->_defaultRender);
        //Assign to the render the list created previously.
        $this->view->assign('mylist', $mylist);
    }

    /**
     * Export data according to given parameters.
     *
     * @return void
     */
    public function toExcelAction()
    {
        $this->filename = 'Documentations.xlsx';

        $tables = array(
            'DocumentationsCategoryIndex' => array('DCI_ID','DCI_Name', 'DCI_Description'),
            'DocumentationsIndex' => array('DI_ID','DI_Name','DI_Url')
        );

        $this->filters = array();
        $this->view->params = $this->_getAllParams();
        $actionKey = $this->_getParam('actionKey');


        // 'documentations'       => 'DocumentationsObject',
        // 'category'       => 'DocumentationsCategoryObject',

        if($actionKey=="documentations"){
            $this->fields = array(
                'DI_Name' => array('width' => '','label' => ''),
                'DI_Url' => array('width' => '','label' => ''),
                'DCI_Name' => array('width' => '','label' => ''));
            $this->select = $this->_db->select()
                    ->from('DocumentationsIndex')
                    ->join('DocumentationsData','DD_ID = DI_ID')
                    ->join('DocumentationsCategoryIndex','DCI_ID = DD_Category')
                    ->where('DI_LanguageID = ?', $this->_defaultEditLanguage)
                    ->where('DCI_LanguageID = ?', $this->_defaultEditLanguage);
            parent::toExcelAction();
       }
       else{
            $this->fields = array(
                'DCI_Name' => array('width' => '','label' => ''),
                'DCI_Description' => array('width' => '','label' => ''));
            $this->select = $this->_db->select()
                    ->from('DocumentationsCategoryIndex')
                    ->where('DCI_LanguageID = ?', $this->_defaultEditLanguage);
            parent::toExcelAction();
        }
    }

    /**
     * Format the current action name to bu used for label texts translations.
     *
     * @access private
     *
     * @return void
     */
    private function _formatName()
    {
        $this->_labelSuffix = str_replace(array('/', '-'), '_', $this->_currentAction);
    }

    /**
     * Reditects the current action to the "real" action to process.
     *
     * @access public
     *
     * @return void
     */
    private function _redirectAction()
    {
        //Redirect to the real action to process If no actionKey = list page.
       // echo $this->_actionKey;
       //exit;
        switch ($this->_actionKey)
        {
            case 'add':
                $this->addAction();                
                $this->_helper->viewRenderer->setRender('add');
                break;
            case 'edit':
                $this->editAction();
                $this->_helper->viewRenderer->setRender('edit');
                break;
            case 'delete':
                $this->deleteAction();
                $this->_helper->viewRenderer->setRender('delete');
                break;
            default:
                if(isset($this->_actionKey)){
                    $this->_listAction($this->_objectList[$this->_actionKey]);
                }
                else{
                    $this->_listAction($this->_objectList[$this->_currentAction]);
                }
                break;
        }
    }

    /**
     * Set options array or the list view. Options are the actions in the page.
     *
     * @access public
     *
     * @param int $tabId Id of the row to be processed.
     * @param int $page  Id of the page if selected with the paginator.
     *
     * @return void
     */
    private function _setActionsList($tabId, $page = 1)
    {
        $commands = array();
        $actions = array();
        $actionPanel = array(
            'width' => '50px'
        );

        $options = array();

        if (count($this->_actionsList) == 0)
        {
            $this->_actionsList = array(

                array('commands' => 'add'),
                array('action_panel' => 'edit-list', 'edit', 'delete')
            );
            if ($this->_addActions)
                $this->_actionsList[1] = array_merge($this->_actionsList[1], $this->_addActions);

            foreach ($this->_actionsList as $key => $controls)
            {
                foreach ($controls as $key => $action)
                {
                    //Redirect to the real action to process If no actionKey = list page.
                    switch ($action)
                    {
                        case 'add':
                            $urlOptions = array(
                                'controller' => $this->_name,
                                'action' => $this->_currentAction,
                                'actionKey' => 'add');
                            if (!empty($filter))
                            {
                                $urlOptions['group-filter'] = $filter;
                            }
                            $commands = array(
                                $this->view->link($this->view->url(
                                    array(
                                        'controller' => $this->_name,
                                        'action' => $this->_currentAction,
                                        'actionKey' => 'add')),
                                    $this->view->getCibleText('button_add'),
                                    array('class' => 'action_submit add'))
                            );

                            break;

                        case 'edit':
                            $url = $this->view->url(array(
                                'action' => $this->_currentAction,
                                'actionKey' => 'edit',
                                $this->_ID => 'xIDx'
                            ));

                            $edit = array(
                                'label' => $this->view->getCibleText('button_edit'),
                                'url' => $url,
                                'findReplace' => array(
                                    array(
                                        'search' => 'xIDx',
                                        'replace' => $tabId
                                    )
                                ),
                                'returnUrl' => $this->view->Url() . "/"
                            );
                            $actions['edit'] = $edit;
                            break;

                        case 'delete':
                            $url = $this->view->url(array(
                                'action' => $this->_currentAction,
                                'actionKey' => 'delete',
                                $this->_ID => 'xIDx'

                            ));
                            $delete = array(
                                'label' => $this->view->getCibleText('button_delete'),
                                'url' => $url,
                                'findReplace' => array(
                                    array(
                                        'search' => 'xIDx',
                                        'replace' => $tabId
                                    )
                                )
                                );

                            $actions['delete'] = $delete;
                            break;

                        case 'log':
                            $url = $this->view->url(array(
                                'action' => $this->_currentAction,
                                'actionKey' => 'log',
                                $this->_ID => 'xIDx'

                            ));

                            $log = array(
                                'label' => $this->view->getCibleText('button_log'),
                                'url' => $url,
                                'findReplace' => array(
                                    array(
                                        'search' => 'xIDx',
                                        'replace' => $tabId
                                    )
                                ),
                                'returnUrl' => $this->view->Url() . "/"
                            );
                            $actions['log'] = $log;
                            break;

                        default:

                            break;
                    }
                }
            }
            $actionPanel['actions'] = $actions;

            $options = array(
                'commands' => $commands,
                'action_panel' => $actionPanel
            );
            if ($this->_disableExportToExcel)
                $options['disable-export-to-excel'] = 'true';
            if ($this->_disableExportToPDF)
                $options['disable-export-to-pdf'] = 'true';
            if ($this->_disableExportToCSV)
                $options['disable-export-to-csv'] = 'true';
            if ($this->_enablePrint)
                $options['enable-print'] = 'true';
            if (!empty($this->_filterData))
                $options['filters'] = $this->_filterData;
            if ($this->_renderPartial)
                $options['renderPartial'] = $this->_renderPartial;

            $options['actionKey'] = $this->_currentAction;
        }
        else
            $options = $this->_actionsList;

        return $options;
    }

    /**
     * Transforms data of the posted form in one array
     *
     * @param array $formData Data to save.
     *
     * @return array
     */
    protected function _mergeFormData(array $formData)
    {
        (array)$tmpArray = array();

        foreach($formData as $key => $data)
        {
            if(is_array($data))
            {
                $tmpArray = array_merge($tmpArray,$data);
            }
            else
                $tmpArray[$key] = $data;
        }

        return $tmpArray;
    }

    /**
     * Add some data from other table, tests the joinTables
     * property. If not empty add tables and join clauses.
     *
     * @param Zend_Db_Table_Select $select
     * @param array $params
     *
     * @return Zend_Db_Table_Select
     */
    private function _addJoinQuery($select, array $params = array())
    {
        if (isset($params['joinTables']) && count($params['joinTables']))
            $this->_joinTables = $params['joinTables'];

        /* If needs to add some data from other table, tests the joinTables
         * property. If not empty add tables and join clauses.
         */
        if (count($this->_joinTables) > 0)
        {
            // Loop on tables list(given by object class) to build the query
            foreach ($this->_joinTables as $key => $object)
            {
                if (is_array($object)){
                    $objName = $object['obj'];
                    $foreignKey = $object['foreignKey'];
                }
                else{
                    $objName = $object;
                    if($objName=="ProductsInfoObject"){
                        $foreignKey = "APBP_Productinfo";
                    }
                    elseif($objName=="BranchesObject"){
                        $foreignKey = "APBP_Branch";
                    }
                    else{
                        $foreignKey = $params['foreignKey'];
                    }
                }
                //Create an object and fetch data from object.
                $tmpObject = new $objName();
                $tmpDataTable = $tmpObject->getDataTableName();
                $tmpIndexTable = $tmpObject->getIndexTableName();
                $tmpColumnData = $tmpObject->getDataColumns();
                $tmpColumnIndex = $tmpObject->getIndexColumns();
                //Add data to tables list
                $tables[$tmpDataTable] = $tmpColumnData;
                $tables[$tmpIndexTable] = $tmpColumnIndex;
                //Get the primary key of the first data object to join table
                $tmpDataId = $tmpObject->getDataId();
                // If it's the first loop, join first table to the current table


                //LEFT JOIN `DocumentationsCategoryData` ON DCD_ID = DD_ID LEFT JOIN `DocumentationsCategoryIndex` ON DCD_ID = DCI_ID


                if ($key == 0)
                {
                    $select->joinLeft($tmpDataTable, $tmpDataId . ' = ' . $foreignKey);
                    //If there's an index table then it too and filter according language
                    if (!empty($tmpIndexTable))
                    {
                        $tmpIndexId = $tmpObject->getIndexId();
                        $select->joinLeft(
                            $tmpIndexTable, $tmpDataId . ' = ' . $tmpIndexId);
                        $select->where(
                            $tmpIndexTable . '.' . $tmpObject->getIndexLanguageId() . ' = ?', $this->_defaultEditLanguage);
                    }
                }
                elseif ($key > 0){
                    // We have an other table to join to previous.
                    $tmpDataId = $tmpObject->getDataId();

                    $select->joinLeft(
                        $tmpDataTable, $tmpDataId . ' = ' . $foreignKey);

                    if (!empty($tmpIndexTable))
                    {
                        $tmpIndexId = $tmpObject->getIndexId();
                        $select->joinLeft(
                            $tmpIndexTable,
                            $tmpDataId . ' = ' . $tmpIndexId);

                        $select->where(
                            $tmpIndexTable . '.' . $tmpObject->getIndexLanguageId() . ' = ?', $this->_defaultEditLanguage);
                    }
                }
            }
        }

        return $select;
    }


    /**
     * Method to reset item sequence call via url only
     */
    public function orderItemAction()
    {
        $oItem = new ItemsObject();

        $items = $oItem->getAll();
        $seq = 0;
        $prevProd = null;
        foreach ($items as $key => $item)
        {
            $prod = $item['I_ProductID'];
            if ($prod == $prevProd)
            {
                $seq += 10;
                $data['I_Seq'] = $seq;
                $oItem->save($item['I_ID'], $data, 1);
            }
            else
            {
                $seq = 10;
                $data['I_Seq'] = $seq;
                $oItem->save($item['I_ID'], $data, 1);
            }
            $prevProd = $item['I_ProductID'];
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
        $associationId = $this->_getParam('associationID');

        $associationSetId = $this->_getParam('associationSetID');
        $cieId = $this->_getParam('listSrc');

        if ($associationAction == "new")
        {
            $optionsData = array();


            if (in_array("products", $this->_associationIds))
            {
                $nameAssoc = $this->_objectList["products"];

                $oData = new $nameAssoc();
                $optionsData = $oData->getList(true, null, true);
            }



            $ID = 'CTD_';
            $Name = 'CTI_Name';
            $labelAssociated = $this->view->getCibleText('form_label_CTI_Name_associate');

            if($associationSetId=="associations"){
                $ID = 'P_';
                $Name = 'PI_Name';
                $labelAssociated = $this->view->getCibleText('form_label_CPPI_Name_associate');
            }
            $newElement = Cible_FunctionsAssociationElements::getNewAssociationSetBox(
                $associationSetId,
                $ID,
                $Name,
                $associationId,
                $$labelAssociated,
                $optionsData,
                array(),
                true);
            echo(Zend_Json::encode(array('newElement' => $newElement)));
        }
    }

    private function _saveImgData($formData, $recordID)
    {
        // Process for additionnal images
        foreach ($formData as $img)
        {
            if ($img[$this->_imageSrc] <> '')
                $this->_setImage($this->_imageSrc, $img, $recordID);
        }
    }
}