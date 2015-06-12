<?php
/**
* Make the management of the directors of the extranet
*
* The system can view, add, edit and remove directors of the extranet. It also allows an administrator to associate one or several groups to provide access rights.
*
* PHP versions 5
*
* LICENSE:
*
* @category   Controller
* @package    Default



* @version    CVS: <?php $ ?> Id:$
*/
class AdministratorController extends Cible_Extranet_Controller_Module_Action
{
    function indexAction()
    {
        // NEW LIST GENERATOR CODE //
        $tables = array(
                'Extranet_Users' => array('EU_ID','EU_LName','EU_FName','EU_Email')
        );

        $field_list = array(
            'EU_FName' => array(
                'width' => '300px'
            ),
            'EU_LName' => array(
                'width' => '300px'
            ),
            'EU_Email' => array(
                'width' => '300px'
            )
        );
        $administratorData = new ExtranetUsers();

        try
        {
            $dbs = Zend_Registry::get('dbs');
            $db = $dbs->getDb('admins');
            $adminCible = new ExtranetUsers(array('db' => $db));
            $adminsCible = $adminCible->getAdminEqualOrOver($this->view->IsAdministrator(),
                true);
            foreach($adminsCible as $key => $value)
            {
                $admins[$value['EU_ID']] = $value;
                $data = $administratorData->setId($value['EU_ID'])->populate();
                if(empty($data))
                {
                    $admins[$value['EU_ID']]['disabled'] = true;
                }
            }
            $adminData = $administratorData->getAdminEqualOrOver($this->view->IsAdministrator(),
                true);
        }
        catch(Exception $exc)
        {
            $admins = array();
        }
        $select = $administratorData->getAdminEqualOrOver($this->view->IsAdministrator());

        $options = array(
            'commands' => array(
                $this->view->link($this->view->url(array('controller'=>'administrator','action'=>'add')),$this->view->getCibleText('button_add_administrators'), array('class'=>'action_submit add') )
            ),
            //'disable-export-to-excel' => 'true',
            'action_panel' => array(
                'width' => '50',
                'actions' => array(
                    'edit' => array(
                        'label' => $this->view->getCibleText('button_edit'),
                        'url' => "{$this->view->baseUrl()}/default/administrator/edit/administratorID/%ID%",
                        'findReplace' => array(
                            'search' => '%ID%',
                            'replace' => 'EU_ID'
                        )
                    ),
                    'delete' => array(
                        'label' => $this->view->getCibleText('button_delete'),
                        'url' => "{$this->view->baseUrl()}/default/administrator/delete/administratorID/%ID%",
                        'findReplace' => array(
                            'search' => '%ID%',
                            'replace' => 'EU_ID'
                        )
                    )
                )
            )
        );
        $options['adapter'] = 'arrayAdmin';
        $options['adapterData'] = $admins;
        $mylist = New Cible_Paginator($select, $tables, $field_list, $options);

        $this->view->assign('mylist', $mylist);
        $this->view->assign('adminCible', $admins);
    }

    function editAction()
    {
        // page title
        $this->view->title = "Profil de l'administrateur";

        // get param
        $administratorID = $this->_getParam('administratorID');
        $order           = $this->_getParam('order');
        $tablePage       = $this->_getParam('tablePage');
        $search          = $this->_getParam('search');

        $paramsArray = array("order" => $order, "tablePage" => $tablePage, "search" => $search);
        $sites = $this->view->siteList(array('getValues' => true));
        $dbs = Zend_Registry::get('dbs');

        // get user data
        $userData = Cible_FunctionsAdministrators::getAdministratorData($administratorID);

        // get group data
        $groupsData = Cible_FunctionsAdministrators::getAllAdministratorGroups();


        /********** ACTIONS ***********/
        $returnLink = $this->view->url(array('controller' => 'administrator', 'action' => 'index', 'administratorID' => null));
        $form = new FormExtranetUser(array(
            'baseDir'   => $this->view->baseUrl(),
            'cancelUrl' => "$returnLink",
            'userId' =>  $administratorID
            ),
            $groupsData->toArray(),
            $this->view->isAdministrator()
        );

        $this->view->assign('administratorID', $administratorID);
        $this->view->assign('form', $form);

        if ( !$this->_request->isPost() ){

            $userGroups = Cible_FunctionsAdministrators::getAllUserGroups($administratorID);
            $optionsList = array();
            $tmpList = explode('|', $userData->EU_SiteAccess);

            foreach ($tmpList as $value)
                if (isset($sites[$value]))
                    $optionsList[$value] = $sites[$value];

            $form->getElement('EU_DefaultSite')->addMultiOptions($optionsList);
            $userData->EU_SiteAccess = $tmpList;

            $groupIDArray = array();
            $i = 0;
            foreach ($userGroups as $userGroup){
                $groupIDArray[$i] = $userGroup['EUG_GroupID'];
                $i++;
            }
            $form->getElement('groups')->setValue($groupIDArray);

            $form->populate($userData->toArray());
        }
        else {
            $formData = $this->_request->getPost();

            if ($form->isValid($formData)) {
                // validate username is unique
                // save user information
                    foreach ($sites as $site => $siteName)
                    {
                        $db = $dbs->getDb($site);
                        // get user data
                        $userData = Cible_FunctionsAdministrators::getAdministratorData($administratorID, $db);

                        $selectedSites = implode('|',$form->getValue('EU_SiteAccess'));

                        $userData['EU_LName']       = $form->getValue('EU_LName');
                        $userData['EU_FName']       = $form->getValue('EU_FName');
                        $userData['EU_Email']       = $form->getValue('EU_Email');
                        $userData['EU_Username']    = $form->getValue('EU_Username');
                        $userData['EU_ShowError']   = $form->getValue('EU_ShowError');
                        $userData['EU_DefaultSite'] = $form->getValue('EU_DefaultSite');
                        $userData['EU_SiteAccess']  = $selectedSites;

                        if ($form->getValue('EU_Password') <> ""){
                            $userData['EU_Password']  = md5($form->getValue('EU_Password'));
                        }

                        $userData->save();


                        // delete all user and group association for that user
                        $userGroups = new ExtranetUsersGroups(array('db' => $db));
                        $where = 'EUG_UserID = ' . $administratorID;
                        $userGroups->delete($where);

                        // insert all user and group association for that user
                        if ($formData['groups']){
                            foreach ($formData['groups'] as $group){
                                $userGroupAssociationData = new ExtranetUsersGroups(array('db' => $db));

                                $row = $userGroupAssociationData->createRow();
                                $row->EUG_UserID    =   $administratorID;
                                $row->EUG_GroupID   =   $group;

                                $row->save();
                            }
                        }
                    }
                    header("location:".$returnLink);
            }
            else
            {
                $optionsList = array();
                $tmpList = $formData['EU_SiteAccess'];
                foreach ($tmpList as $value)
                    $optionsList[$value] = $sites[$value];

                $form->getElement('EU_DefaultSite')->addMultiOptions($optionsList);
            }
        }
    }

    function addAction()
    {
        // page title
        $this->view->title = "Ajout d'un administrateur";

        // get group data
        $groupsData = Cible_FunctionsAdministrators::getAllAdministratorGroups();

        /********** ACTIONS ***********/
        $returnLink = $this->view->url(array('controller' => 'administrator', 'action' => 'index'));
        $form = new FormExtranetUser(array(
            'baseDir'   => $this->view->baseUrl(),
            'cancelUrl' => "$returnLink"
            ),
            $groupsData->toArray(),
            $this->view->isAdministrator()
        );

        $form->getElement('cancel')->setAttrib('onclick', 'document.location.href="'.$returnLink.'"');
        $form->getElement("EU_Password")->setRequired(true);
        $form->getElement("EU_Password")->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => "Veuillez entrer un mot de passe")));
        $this->view->form = $form;
        $sites = $this->view->siteList(array('getValues' => true));
        $dbs = Zend_Registry::get('dbs');

        if ($this->_request->isPost() ){
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                // validate username is unique
                    foreach ($sites as $site => $siteName)
                    {
                        $newInsertID = 0;
                        $db = $dbs->getDb($site);
                        $userData = new ExtranetUsers(array('db' => $db));
                        $row = $userData->createRow();
                        $selectedSites = implode('|',$form->getValue('EU_SiteAccess'));
                        $row->EU_LName      = $form->getValue('EU_LName');
                        $row->EU_FName      = $form->getValue('EU_FName');
                        $row->EU_Email      = $form->getValue('EU_Email');
                        $row->EU_Username   = $form->getValue('EU_Username');
                        $row->EU_Password   = md5($form->getValue('EU_Password'));
                        $row->EU_DefaultSite  = $form->getValue('EU_DefaultSite');
                        $row->EU_SiteAccess  = $selectedSites;
                        $row->EU_ShowError  = $form->getValue('EU_ShowError');
                        $newInsertID = $row->save();
                        // insert all user and group association for that user
                        if ($formData['groups']){
                            foreach ($formData['groups'] as $group){
                                $userGroupAssociationData = new ExtranetUsersGroups(array('db' => $db));

                                $rowGroup = $userGroupAssociationData->createRow();
                                $rowGroup->EUG_UserID    =   $newInsertID;
                                $rowGroup->EUG_GroupID   =   $group;

                                $rowGroup->save();
                            }
                        }
                    }
                    header("location:".$returnLink);
            }
            else
            {
                if (!empty($formData['EU_SiteAccess']))
                {
                    $optionsList = array();
                    $tmpList = $formData['EU_SiteAccess'];
                    foreach ($tmpList as $value)
                        $optionsList[$value] = $sites[$value];

                    $form->getElement('EU_DefaultSite')->addMultiOptions($optionsList);
                }
            }
        }
    }

    function deleteAction()
    {
        // set page title
        $this->view->title = "Supprimer un administrateur";

        // get params
        $administratorID = (int)$this->_getParam( 'administratorID' );

        if ($this->_request->isPost()) {
            // if is set delete, then delete
            $delete = isset($_POST['delete']);
            $returnLink = $this->view->url(array('controller' => 'administrator', 'action' => 'index', 'administratorID' => null));
            if ($delete && $administratorID > 0) {
                $sites = $this->view->siteList(array('getValues' => true));
                $dbs = Zend_Registry::get('dbs');
                foreach ($sites as $site => $siteName)
                {
                    $db = $dbs->getDb($site);
                    $user = new ExtranetUsers(array('db' => $db));
                    $where = 'EU_ID = ' . $administratorID;
                    $user->delete($where);
                    // delete all user and group association for that user
                    $userGroups = new ExtranetUsersGroups(array('db' => $db));
                    $where = 'EUG_UserID = ' . $administratorID;
                    $userGroups->delete($where);
                }

            }
            //$this->_redirect($returnLink);
            header("location:".$returnLink);
        }
        else
        {
            if ($administratorID > 0) {
                $administrator = new ExtranetUsers();
                $this->view->administrator = $administrator->fetchRow('EU_ID='.$administratorID);
            }
        }
    }

    public function toExcelAction(){
        $this->filename = 'Administrators.xlsx';

        $this->tables = array(
                'Extranet_Users' => array('EU_ID','EU_LName','EU_FName','EU_Email')
        );

        $this->fields = array(
            'EU_FName' => array(
                'width' => '',
                'label' => ''
            ),
            'EU_LName' => array(
                'width' => '',
                'label' => ''
            ),
            'EU_Email' => array(
                'width' => '',
                'label' => ''
            )
        );

        $this->filters = array(

        );

        $administratorData = new ExtranetUsers();
        $this->select = $administratorData->select();

        parent::toExcelAction();
    }

    public function profileAction(){
        // page title
        $this->view->title = "Votre profil";
        $sites = $this->view->siteList(array('getValues' => true));
        $dbs = Zend_Registry::get('dbs');
        // get user data
        $authData = $this->view->user;
        $authID     = $authData['EU_ID'];

        $users = new ExtranetUsers();
        $select = $users->select()
        ->where("EU_ID = ?", $authID);


        $userData = $users->fetchRow($select);

        if($userData==""){
            echo "<br /><font style='font-size:18px;'> Vous êtes un super utilisateur.<br />Vous ne pouvez gérer vos informations ici.<br/>"
            . "Vous pouvez modifier vos informations dans la base 'cible_admin'</font> ";

        }
        else{
        /********** ACTIONS ***********/
        $form = new FormExtranetUser(array(
            'baseDir'   => $this->view->baseUrl(),
            'cancelUrl' => $this->getFrontController()->getBaseUrl(),
            'userId' => $authID,
            'profile' => true
        ),array(), $this->view->isAdministrator());
        $this->view->form = $form;

        if ( !$this->_request->isPost() ){
            $form->populate($userData->toArray());
        }
        else {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                // save user information
                foreach ($sites as $site => $siteName)
                {
                    $db = $dbs->getDb($site);
                    // get user data
                    $userData = Cible_FunctionsAdministrators::getAdministratorData($authID, $db);

                    $userData['EU_LName']     = $form->getValue('EU_LName');
                    $userData['EU_FName']     = $form->getValue('EU_FName');
                    $userData['EU_Email']     = $form->getValue('EU_Email');
                    $userData['EU_Username']  = $form->getValue('EU_Username');
                    $userData['EU_ShowError'] = $form->getValue('EU_ShowError');

                    if ($form->getValue('EU_Password') <> ""){
                        $userData['EU_Password']  = md5($form->getValue('EU_Password'));
                    }

                    $userData->save();
                }
                $this->_redirect('');
            }
        }
    }
}
}
