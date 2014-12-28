<?php

    class Text_IndexController extends Cible_Controller_Block_Abstract
    {
        protected $_moduleID = 1;

        public function init(){
            parent::init();
        }

        public static function getModulePermission()
        {
            return array('foobar');
        }

        protected function add($blockID){
             // add text
            $Languages = Cible_FunctionsGeneral::getAllLanguage();

            foreach($Languages as $Lang){
                $textData = new Text();
                $text = $textData->createRow();
                $text->TD_BlockID = $blockID;
                $text->TD_LanguageID = $Lang["L_ID"];

                $text->save();
            }
        }

        protected function delete($blockID){
            /* DELETE INDEXATION */
            $textSelect = new Text();
            $select = $textSelect->select()
            ->where('TD_BlockID = ?', $blockID);

            $textData = $textSelect->fetchAll($select);
            foreach($textData as $text){
                $indexData['moduleID']  = $this->_moduleID;
                $indexData['contentID'] = $text['TD_ID'];
                $indexData['languageID'] = $text['TD_LanguageID'];
                $indexData['action']    = 'delete';

                Cible_FunctionsIndexation::indexation($indexData);
            }

            /* DELETE ROW IN TEXTDATA TABLE   */
            $textData   = new Text();
            $where      = 'TD_BlockID = ' . $blockID;
            $textData->delete($where);
        }


        public function setOnlineBlockAction(){
            parent::setOnlineBlockAction();
            $blockID = $this->getRequest()->getParam('blockID');

            // index the new text if block online
            $textSelect = new Text();
            $select = $textSelect->select()->setIntegrityCheck(false)
            ->from('TextData')
            ->where('TD_BlockID = ?',$blockID)

            ->join('Blocks','B_ID = TD_BlockID')
            ->join('PagesIndex','PI_PageID = B_PageID')
            ->where('PI_LanguageID = TD_LanguageID');
            $textData = $textSelect->fetchAll($select);
            foreach($textData as $text){

                $indexData['pageID']    = $text['B_PageID'];
                $indexData['moduleID']  = $text['B_ModuleID'];
                $indexData['contentID'] = $text['TD_ID'];
                $indexData['languageID'] = $text['TD_LanguageID'];
                $indexData['title']     = $text['PI_PageTitle'];
                $indexData['text']      = '';
                $indexData['link']      = '';
                $indexData['object']  = 'TextObject';
                $indexData['contents']  = $text['PI_PageTitle'] . " " . $text['TD_OnlineText'];

                if($text['PI_Status'] == 1){
                    if($text['B_Online'] == 1)
                        $indexData['action'] = 'add';
                    else
                        $indexData['action'] = 'delete';
                }
                else
                    $indexData['action'] = 'delete';


                Cible_FunctionsIndexation::indexation($indexData);
            }

        }


        public function addAction(){
            throw new Exception('Not implemented');
        }

        public function editAction(){
            $this->view->title = "Modification d'un texte";
            if ($this->view->aclIsAllowed('text','edit',true)){
                $_blockID = $this->_getParam('blockID');
                $_pageid = $this->_getParam('pageID');
                $_id = $this->_getParam('ID');
                $base_dir = $this->getFrontController()->getBaseUrl();

                $blockText = new Text();
                $select = $blockText->select();
                $select->where('TD_BlockID = ?',$_blockID);
                $select->where('TD_LanguageID = ?', $this->_currentEditLanguage);
                $block = $blockText->fetchRow($select);
                $blockSelect = new Blocks();
                $selectBloc = $blockSelect->select()->setIntegrityCheck(false)
                ->from('Blocks')
                ->where('B_ID = ?', $_blockID)
                ->join('PagesIndex', 'PI_PageID = B_PageID')
                ->where('PI_LanguageID = ?', $block['TD_LanguageID']);

                // If block doesn't exist, creates it.
                if( empty($block) )
                {
                    $block = $blockText->createRow(array(
                        'TD_BlockID' => $_blockID,
                        'TD_LanguageID' => $this->_currentEditLanguage
                    ));

                    $block->save();

                    // load it
                    $block = $blockText->fetchRow($select);
                }

                if($_id)
                    $returnLink = "$base_dir/text/index/list-approbation-request/";
                    else
                    $returnLink = "$base_dir/page/index/index/ID/$_pageid";

                $form = new FormText(array(
                    'baseDir' => $base_dir,
                    'pageID' => $_pageid,
                    'cancelUrl' => $returnLink,
                    'toApprove' => $block["TD_ToApprove"],
                    'mode' => 'edit'
                ));

                if ( !$this->_request->isPost() ){
                    $block_data = empty($block) ? array() : $block->toArray();
                    $blockData = $blockSelect->fetchRow($selectBloc);
                    $block_data['PI_PageTitle'] = $blockData['PI_PageTitle'];
                    $form->populate($block_data);
                } else {

                    $formData = $this->_request->getPost();     if ($form->isValid($formData)) {
                        if (isset($_POST['submitSaveSubmit'])){
                            $block['TD_ToApprove'] = 1;
                            //header("location:".$returnLink);
                        }
                        elseif (isset($_POST['submitSaveReturnWriting'])){
                            $block['TD_ToApprove'] = 0;
                            //header("location:".$returnLink);
                        }
                        elseif(isset($_POST['submitSaveOnline'])){
                            $block['TD_OnlineTitle'] = $formData['TD_DraftTitle'];
                            $block['TD_OnlineText'] =  $formData['TD_DraftText'];
                            $block['TD_ToApprove'] = 0;
                            //header("location:".$returnLink);

                            // index the new text if block online
                            $blockData = $blockSelect->fetchRow($selectBloc);

                            if($blockData['B_Online'] == 1 && $blockData['PI_Status'] == 1){
                                $indexData['pageID']    = $blockData['B_PageID'];
                                $indexData['moduleID']  = $blockData['B_ModuleID'];
                                $indexData['contentID'] = $block['TD_ID'];
                                $indexData['languageID'] = $block['TD_LanguageID'];
                                $indexData['title']     = $blockData['PI_PageTitle'];
                                $indexData['text']      = '';
                                $indexData['link']      = '';
                                $indexData['object']  = 'TextObject';
                                $indexData['contents']  = $blockData['PI_PageTitle'] . " " . $block['TD_OnlineText'];
                                $indexData['action']    = 'update';

                                Cible_FunctionsIndexation::indexation($indexData);
                            }

                        }
                        else{
                            $returnLink = "";
                        }

                        $block['TD_DraftTitle'] = $formData['TD_DraftTitle'];
                        $block['TD_DraftText'] =  $formData['TD_DraftText'];

                        $block->save();

                        $oPage = new PagesObject();
                        $pageData['P_ID'] = $_pageid;
                        $pageData['PI_PageTitle'] = $formData['PI_PageTitle'];

                        $oPage->save($_pageid, $pageData, $this->_currentEditLanguage);


                        //if($returnLink <> "")
                            //$this->_redirect("/page/index/index/ID/$_pageid");
                    }
                }

                $this->view->assign('form', $form);
                $this->view->assign('pageId', $_pageid);
                $this->view->assign('onlineTitle', isset($block['TD_OnlineTitle']) ? $block['TD_OnlineTitle'] : '');
                $this->view->assign('onlineText', isset($block['TD_OnlineText']) ? $block['TD_OnlineText'] : '');
            }

        }

        public function deleteAction(){
            throw new Exception('Not implemented');
        }


        public function listApprobationRequestAction(){
            if( $this->view->aclIsAllowed('text', 'publish') ){

                 $tables = array(
                        'TextData' => array('TD_ID', 'TD_BlockID', 'TD_DraftTitle'),
                        'Blocks' => array('B_PageID'),
                        'BlocksIndex' => array('BI_BlockTitle'),
                        'Languages' => array('L_Suffix')
                );

                $field_list = array(
                    'BI_BlockTitle' => array(
                    )
                );

                $select = $this->_db->select()->from('TextData', $tables['TextData'])
                                        ->joinRight('Blocks', 'Blocks.B_ID = TextData.TD_BlockID')
                                        ->joinLeft('BlocksIndex', 'BlocksIndex.BI_BlockID = Blocks.B_ID', 'BI_BlockTitle')
                                        ->joinLeft('Languages', 'Languages.L_ID = TextData.TD_LanguageID')
                                        ->where('TextData.TD_LanguageID = BlocksIndex.BI_LanguageID')
                                        ->where('TextData.TD_ToApprove = ?', 1);

                $options = array(
                    'disable-export-to-excel' => 'true',
                    'action_panel' => array(
                        'width' => '50',
                        'actions' => array(
                            'edit' => array(
                                'label' => $this->view->getCibleText('button_edit'),
                                'url' => "{$this->view->baseUrl()}/text/index/edit/pageID/%PageID%/blockID/%BlockID%/ID/%ID%/lang/%LANG%",
                                'findReplace' => array(
                                     array(
                                        'search' => '%ID%',
                                        'replace' => 'TD_ID'
                                    ),
                                    array(
                                        'search' => '%BlockID%',
                                        'replace' => 'TD_BlockID'
                                    ),
                                    array(
                                        'search' => '%PageID%',
                                        'replace' => 'B_PageID'
                                    ),
                                    array(
                                        'search' => '%LANG%',
                                        'replace' => 'L_Suffix'
                                    )
                                )
                            )
                        )
                    )
                );

                $mylist = New Cible_Paginator($select, $tables, $field_list, $options);

                $this->view->assign('mylist', $mylist);
            }
        }

        public function listAction(){
            $type = $this->_getParam('site');
            $this->view->type = 's';
            if (!empty($type))
                $this->view->type = $type;

            $this->view->assign('langId', $this->_defaultEditLanguage);

        }

    public function parseContentAction()
    {
        $replace = array(
            "http://www.csss-iugs.ca/cdrv/" => "http://www.cdrv.ca",
            "http://www.csss-iugs.ca/cdrv/index.php?L=fr" => "http://www.cdrv.ca",
            "http://www.csss-iugs.ca/cdrv/index.php?L=en" => "http://www.cdrv.ca/home",
//            "cdrv/" => "http://www.cdrv.ca" ,
//            "/cdrv/" => "http://www.cdrv.ca",
            "cdrv/?L=fr" => "http://www.cdrv.ca",
            "cdrv/?L=en" => "http://www.cdrv.ca/home",
            "cdrv/index.php?L=fr" => "http://www.cdrv.ca",
            "cdrv/index.php?L=en" => "http://www.cdrv.ca/home",
            "http://www.csss-iugs.ca/fondation/" => "http://www.fondationvitae.ca",
            "http://www.csss-iugs.ca/fondation/?L=fr" => "http://www.fondationvitae.ca",
            "http://www.csss-iugs.ca/fondation/?L=en" => "http://www.fondationvitae.ca/home",
            "http://www.csss-iugs.ca/fondation/index.php?L=fr" => "http://www.fondationvitae.ca",
            "http://www.csss-iugs.ca/fondation/index.php?L=en" => "http://www.fondationvitae.ca/home",
//            "fondation/" => "http://www.fondationvitae.ca",
            "fondation/?L=fr" => "http://www.fondationvitae.ca",
            "fondation/?L=en" => "http://www.fondationvitae.ca/home",
            "fondation/index.php?L=fr" => "http://www.fondationvitae.ca",
            "fondation/index.php?L=en" => "http://www.fondationvitae.ca/home",
            "http://www.csss-iugs.ca/" => "http://www.csss-iugs.ca",
            "/index.php?L=fr" => "",
            "/index.php?L=en" => "home",
            '/cdrv/from_fckeditor/fichiers/' => 'cdrv/data/files/',
            '/fondation/from_fckeditor/fichiers/' => 'fondation/data/files/',
            '/images/from_fckeditor/fichiers/' => 'c3s/data/files/',
            '/cdrv/from_fckeditor/flash/' => 'cdrv/data/files/',
            '/fondation/from_fckeditor/flash/' => 'fondation/data/files/',
            '/images/from_fckeditor/flash/' => 'c3s/data/files/',
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=&amp;Niveau4=" => 	"le-csss-iugs-en-bref",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=244&amp;Niveau4=" => 	"services-sociaux-et-de-sante",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=245&amp;Niveau4=" => 	"centres-d-hebergement-et-de-soins-de-longue-duree",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=246&amp;Niveau4=" => 	"hopital-de-soins-specialises-pour-les-personnes-agees",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=395&amp;Niveau4=" => 	"prix-inspiration",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=35&amp;Niveau3=&amp;Niveau4=" => 	"mission-et-valeurs",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=36&amp;Niveau3=&amp;Niveau4=" => 	"plan-d-organisation",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=37&amp;Niveau3=&amp;Niveau4=" => 	"edifices",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=38&amp;Niveau3=&amp;Niveau4=" => 	"historique",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=&amp;Niveau4=" => 	"benevolat",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=103&amp;Niveau4=" => 	"dix-facons-d-aider",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=104&amp;Niveau4=" => 	"les-benevoles-en-action",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=105&amp;Niveau4=" => 	"soutien-aux-benevoles",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=&amp;Niveau4=" => 	"engagement-envers-la-qualite-et-la-securite-des-services",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=41&amp;Niveau4=" => 	"code-d-ethique",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=371&amp;Niveau4=" => 	"double-identification",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=373&amp;Niveau4=" => 	"engagement-envers-la-qualite-et-la-securite-des-services-prevention-des-chutes",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=374&amp;Niveau4=" => 	"prevention-des-infections",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=375&amp;Niveau4=" => 	"prevention-du-suicide",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=42&amp;Niveau3=&amp;Niveau4=" => 	"traitement-des-plaintes",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=42&amp;Niveau3=106&amp;Niveau4=" => 	"cheminement-des-plaintes",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=42&amp;Niveau3=107&amp;Niveau4=" => 	"formuler-une-plainte",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=&amp;Niveau4=" => 	"conseils-et-comites",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=108&amp;Niveau4=" => 	"corporations-du-csss-iugs",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=" => 	"conseil-d-administration",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=247" => 	"soumettre-une-question",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=248" => 	"calendrier-des-rencontres",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=249" => 	"liste-des-membres",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=110&amp;Niveau4=" => 	"comites-d-ethique-de-la-recherche",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=330&amp;Niveau4=" => 	"comite-d-ethique-clinique",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=111&amp;Niveau4=" => 	"comite-des-usagers-et-comites-des-residents",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=112&amp;Niveau4=" => 	"conseil-des-infirmieres-et-infirmiers",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=113&amp;Niveau4=" => 	"conseil-des-infirmieres-et-infirmiers-auxiliaires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=114&amp;Niveau4=" => 	"conseil-des-medecins-dentistes-et-pharmaciens",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=116&amp;Niveau4=" => 	"conseil-multidisciplinaire",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=117&amp;Niveau4=" => 	"conseil-paraprofessionnel",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=118&amp;Niveau4=" => 	"conseil-sages-femmes",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=&amp;Niveau4=" => 	"reseau-local-de-services-et-projet-clinique",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=119&amp;Niveau4=" => 	"projet-clinique-du-rls",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=120&amp;Niveau4=" => 	"forum-des-partenaires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=121&amp;Niveau4=" => 	"structure-de-coordination-du-rls",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=&amp;Niveau4=" => 	"le-csss-iugs-partenaires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=123&amp;Niveau4=" => 	"chus",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=316&amp;Niveau4=" => 	"cess",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=124&amp;Niveau4=" => 	"acteurs-socio-economiques",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=125&amp;Niveau4=" => 	"milieu-scolaire",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=126&amp;Niveau4=" => 	"milieu-universitaire-et-medecins",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=127&amp;Niveau4=" => 	"monde-municipal",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=128&amp;Niveau4=" => 	"observatoires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=" => 	"observatoire-quebecois-des-rls",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=327" => 	"bulletins-oqrls",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=328" => 	"formulaire-d-initiatives",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=376" => 	"initiatives",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=377" => 	"observatoire-quebecois-des-rls-partenaires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=381" => 	"articles-et-presentations-de-l-oqrls",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=129&amp;Niveau4=" => 	"reseau-de-la-sante-et-des-services-sociaux",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=130&amp;Niveau4=" => 	"ressources-communautaires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=309&amp;Niveau4=" => 	"autres-partenaires",
            "show_section.php?L=fr&amp;ParentID=27&amp;Niveau2=46&amp;Niveau3=&amp;Niveau4=" => 	"calendrier-d-evenements",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=329&amp;Niveau3=&amp;Niveau4=" => 	"horaire",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=&amp;Niveau4=" => 	"enfants-adolescents-et-familles",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=131" => 	"soutien-aux-parents-d-enfants-de-0-5-ans",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=131" => 	"soutien-aux-parents-d-enfants-de-0-5-ans",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=132" => 	"centre-de-maternite-sages-femmes",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=133" => 	"groupes-de-medecine-de-famille",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=134" => 	"les-premiers-jours-a-la-maison",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=134" => 	"les-premiers-jours-a-la-maison",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=135" => 	"bebe-trucs",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=136" => 	"allaitement",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=138" => 	"ecole-en-sante",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=138" => 	"ecole-en-sante",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=139" => 	"fluppy",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=140" => 	"sante-dentaire",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=141" => 	"infirmiere-scolaire",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=83&amp;Niveau4=" => 	"enfants-adolescents-et-familles-centre-d-enseignement-sur-l-asthme",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=84&amp;Niveau4=" => 	"clinique-des-jeunes",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=85&amp;Niveau4=" => 	"deficience-intellectuelle-et-troubles-envahissants-du-developpement",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=86&amp;Niveau4=" => 	"enfants-adolescents-et-familles-dependances",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=142&amp;Niveau4=" => 	"difficultes-d-adaptation-familiale-et-sociale",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=87&amp;Niveau4=" => 	"orthophonie",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=87&amp;Niveau4=143" => 	"hanen",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=89&amp;Niveau4=" => 	"enfants-adolescents-et-familles-sante-mentale",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=88&amp;Niveau4=144" => 	"service-d-auxiliaire-en-sante-et-services-sociaux",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=88&amp;Niveau4=144" => 	"service-d-auxiliaire-en-sante-et-services-sociaux",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=88&amp;Niveau4=145" => 	"aide-financiere",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=90&amp;Niveau4=" => 	"enfants-adolescents-et-familles-vaccination",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=&amp;Niveau4=" => 	"adultes",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=91&amp;Niveau4=" => 	"aide-et-soutien",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=93&amp;Niveau4=" => 	"adultes-dependances",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=94&amp;Niveau4=" => 	"itinerance",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=95&amp;Niveau4=" => 	"oncologie",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=146" => 	"saines-habitudes-de-vie-0-5-30",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=146" => 	"saines-habitudes-de-vie-0-5-30",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=147" => 	"centre-d-abandon-du-tabac",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=92" => 	"prevention-et-gestion-des-maladies-chroniques-centre-d-enseignement-sur-l-asthme",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=148" => 	"maladies-pulmonaires-chroniques",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=149" => 	"readaptation-cardiaque",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=315" => 	"diabete",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=97&amp;Niveau4=" => 	"adultes-sante-mentale",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=98&amp;Niveau4=" => 	"ressources-en-hebergement",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=99&amp;Niveau4=" => 	"adultes-soins-infirmiers",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=100&amp;Niveau4=" => 	"services-a-domicile",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=101&amp;Niveau4=" => 	"adultes-vaccination",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=&amp;Niveau4=" => 	"personnes-agees-ou-en-perte-d-autonomie",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=102&amp;Niveau4=" => 	"hebergement",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=102&amp;Niveau4=154" => 	"autres-ressources-en-hebergement",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=155&amp;Niveau4=" => 	"soutien-a-domicile",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=156&amp;Niveau4=" => 	"centre-de-jour",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=157&amp;Niveau4=" => 	"cliniques-ambulatoires-geriatriques",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=167&amp;Niveau4=" => 	"gerontopsychiatrie",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=168&amp;Niveau4=" => 	"hopital-de-jour",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=169&amp;Niveau4=" => 	"hospitalisation-de-courte-duree-ucdg-",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=170&amp;Niveau4=" => 	"personnes-agees-ou-en-perte-d-autonomie-prevention-des-chutes",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=171&amp;Niveau4=" => 	"readaptation-urfi-",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=173&amp;Niveau4=" => 	"personnes-agees-ou-en-perte-d-autonomie-vaccination",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=322&amp;Niveau3=&amp;Niveau4=" => 	"action-communautaire",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=&amp;Niveau4=" => 	"clinique-du-voyageur-international",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=174&amp;Niveau4=" => 	"services-offerts",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=338&amp;Niveau4=" => 	"vaccins-disponibles",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=175&amp;Niveau4=" => 	"frais",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=177&amp;Niveau4=" => 	"clinique-du-voyageur-international-liens-utiles",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=339&amp;Niveau4=" => 	"heures-d-ouverture",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=340&amp;Niveau4=" => 	"nous-joindre",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=166&amp;Niveau3=&amp;Niveau4=" => 	"deficience-physique",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=51&amp;Niveau3=&amp;Niveau4=" => 	"services-dependances",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=52&amp;Niveau3=&amp;Niveau4=" => 	"infections-transmises-sexuellement-et-par-le-sang",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=53&amp;Niveau3=&amp;Niveau4=" => 	"info-sante",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=178&amp;Niveau4=" => 	"besoin-d-un-medecin-de-famille",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=178&amp;Niveau4=" => 	"besoin-d-un-medecin-de-famille",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=361&amp;Niveau4=" => 	"guichet-d-acces-a-un-medecin-de-famille",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=179&amp;Niveau4=" => 	"gmf-des-deux-rives-et-gmf-des-grandes-fourches",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=172&amp;Niveau3=&amp;Niveau4=" => 	"soins-palliatifs",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=55&amp;Niveau3=&amp;Niveau4=" => 	"urgence-detresse",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=56&amp;Niveau3=&amp;Niveau4=" => 	"services-vaccination",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=56&amp;Niveau3=344&amp;Niveau4=" => 	"vaccination-contre-la-grippe-saisonniere",
            "show_section.php?L=fr&amp;ParentID=28&amp;Niveau2=392&amp;Niveau3=&amp;Niveau4=" => 	"formulaires-de-reference-reserves-aux-professionnels",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=&amp;Niveau4=" => 	"les-possibilites-de-carrieres-qui-s-offrent-a-toi",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=188&amp;Niveau4=" => 	"les-possibilites-de-carrieres-qui-s-offrent-a-toi-soins-infirmiers",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=189&amp;Niveau4=" => 	"preposes-aux-beneficiaires-et-auxiliaires-de-la-sante",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=190&amp;Niveau4=" => 	"professionnels-et-techniciens-de-la-sante-et-des-services-sociaux",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=200&amp;Niveau4=" => 	"metiers-et-soutien-technique-et-materiel",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=198&amp;Niveau4=" => 	"autres-professionnels-et-techniciens",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=202&amp;Niveau4=" => 	"personnel-administratif",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=196&amp;Niveau4=" => 	"personnel-de-recherche",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=191&amp;Niveau4=" => 	"personnel-cadre",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=204&amp;Niveau4=" => 	"emplois-d-ete",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=364&amp;Niveau4=" => 	"emplois-disponibles",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=364&amp;Niveau4=" => 	"emplois-disponibles",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=362&amp;Niveau4=" => 	"alerte-emploi",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=366&amp;Niveau4=" => 	"candidatures-spontanees",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=367&amp;Niveau4=" => 	"recruteurs-externes",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=368&amp;Niveau4=" => 	"activites-de-recrutement",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=369&amp;Niveau4=" => 	"mise-a-jour-de-votre-dossier",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=&amp;Niveau4=" => 	"des-conditions-de-travail-avantageuses",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=185&amp;Niveau4=" => 	"au-csss-iugs-nous-concilions-travail-et-vie-personnelle",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=186&amp;Niveau4=" => 	"le-developpement-de-tes-competences-nous-tient-a-coeur",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=187&amp;Niveau4=" => 	"ta-sante-notre-inspiration-au-csss-iugs",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=&amp;Niveau4=" => 	"le-csss-iugs-un-milieu-ouvert",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=181&amp;Niveau4=" => 	"le-developpement-durable-pour-un-environnement-plus-vert",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=183&amp;Niveau4=" => 	"des-technologies-qui-ameliorent-les-soins-et-les-services",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=184&amp;Niveau4=" => 	"decouvrir-la-vie-a-sherbrooke",
            "show_section.php?L=fr&amp;ParentID=29&amp;Niveau2=390&amp;Niveau3=&amp;Niveau4=" => 	"le-csss-iugs-en-quelques-mots",
            "show_section.php?L=fr&amp;ParentID=30&amp;Niveau2=306&amp;Niveau3=&amp;Niveau4=" => 	"fondation-vitae-fondation-vitae",
            "show_section.php?L=fr&ParentID=31&Niveau2=380&Niveau3=&Niveau4=" => 	"acces-a-l-information",
            "show_section.php?L=fr&ParentID=31&Niveau2=62&Niveau3=&Niveau4=" => 	"actualites",
            "show_section.php?L=fr&ParentID=31&Niveau2=64&Niveau3=&Niveau4=" => 	"communiques-de-presse",
            "show_section.php?L=fr&ParentID=31&Niveau2=63&Niveau3=&Niveau4=" => 	"capsules-sante",
            "show_section.php?L=fr&ParentID=31&Niveau2=65&Niveau3=&Niveau4=" => 	"representants-des-medias",
            "show_section.php?L=fr&ParentID=31&Niveau2=66&Niveau3=&Niveau4=" => 	"la-sante-une-passion-a-partager",
            "show_section.php?L=fr&ParentID=31&Niveau2=67&Niveau3=&Niveau4=" => 	"plan-strategique",
            "show_section.php?L=fr&ParentID=31&Niveau2=68&Niveau3=&Niveau4=" => 	"rapports-annuels",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=&Niveau4=" => 	"bibliotheque",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=206&Niveau4=" => 	"bibliotheque-services",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=207&Niveau4=" => 	"politiques-et-reglements",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=208&Niveau4=" => 	"catalogue-cameleon",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=209&Niveau4=" => 	"nouvelles-acquisitions",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=210&Niveau4=" => 	"collections",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=211&Niveau4=" => 	"periodiques",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=212&Niveau4=" => 	"ressources-electroniques",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=213&Niveau4=" => 	"documents-a-la-reserve-pour-les-etudiants",
            "show_section.php?L=fr&ParentID=31&Niveau2=69&Niveau3=214&Niveau4=" => 	"bibliotheque-liens-utiles",
            "show_section.php?L=fr&ParentID=31&Niveau2=70&Niveau3=&Niveau4=" => 	"acces-a-l-information",
            "show_section.php?L=fr&ParentID=31&Niveau2=70&Niveau3=384&Niveau4=" => 	"publications-officielles",
            "show_section.php?L=fr&ParentID=31&Niveau2=70&Niveau3=372&Niveau4=" => 	"depliants",
            "show_section.php?L=fr&ParentID=31&Niveau2=70&Niveau3=383&Niveau4=" => 	"autres-diffusions",
            "show_section.php?L=fr&ParentID=31&Niveau2=323&Niveau3=&Niveau4=" => 	"representants-des-medias",
            "show_section.php?L=fr&ParentID=31&Niveau2=393&Niveau3=&Niveau4=" => 	"la-sante-une-passion-a-partager",
            "show_section.php?L=fr&amp;ParentID=32&amp;Niveau2=252&amp;Niveau3=&amp;Niveau4=" => 	"l-enseignement-au-csss-iugs",
            "show_section.php?L=fr&amp;ParentID=32&amp;Niveau2=252&amp;Niveau3=337&amp;Niveau4=" => 	"umf-estrie",
            "show_section.php?L=fr&amp;ParentID=32&amp;Niveau2=72&amp;Niveau3=&amp;Niveau4=" => 	"etudiants",
            "show_section.php?L=fr&amp;ParentID=32&amp;Niveau2=72&amp;Niveau3=253&amp;Niveau4=" => 	"accueil-des-etudiants",
            "show_section.php?L=fr&amp;ParentID=32&amp;Niveau2=74&amp;Niveau3=&amp;Niveau4=" => 	"maisons-d-enseignement",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=75&amp;Niveau3=&amp;Niveau4=" => 	"centre-de-recherche-sur-le-vieillissement",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=&amp;Niveau4=" => 	"recherche-sociale-du-centre-affilie-universitaire",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=" => 	"axes-de-recherche",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=217" => 	"personnes-en-situation-de-precarite",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=218" => 	"axes-de-recherche-sante-mentale",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=219" => 	"developpement-des-communautes",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=220&amp;Niveau4=" => 	"intervention-de-quartiers",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=331&amp;Niveau4=332" => 	"cahiers-de-recherche",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=331&amp;Niveau4=332" => 	"cahiers-de-recherche",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=331&amp;Niveau4=333" => 	"bulletin-d-information",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=336&amp;Niveau4=" => 	"activites",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=334&amp;Niveau4=" => 	"bourses-d-etudes-desjardins-cau",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=77&amp;Niveau3=&amp;Niveau4=" => 	"autres-projets",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=77&amp;Niveau3=221&amp;Niveau4=" => 	"recherches-en-cours",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=78&amp;Niveau3=&amp;Niveau4=" => 	"ethique-de-la-recherche",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=396&amp;Niveau3=&amp;Niveau4=" => 	"nagano-demandes-d-evaluation-de-projet",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=396&amp;Niveau3=313&amp;Niveau4=" => 	"guides-et-formulaires",
            "show_section.php?L=fr&amp;ParentID=33&amp;Niveau2=396&amp;Niveau3=386&amp;Niveau4=" => 	"politiques-et-procedures",
            "show_section.php?L=fr&amp;ParentID=258&amp;Niveau2=278&amp;Niveau3=&amp;Niveau4=" => 	"chercheurs-chercheurs",
            "show_section.php?L=fr&amp;ParentID=258&amp;Niveau2=302&amp;Niveau3=300&amp;Niveau4=" => 	"publications-scientifiques",
            "show_section.php?L=fr&amp;ParentID=258&amp;Niveau2=302&amp;Niveau3=300&amp;Niveau4=" => 	"publications-scientifiques",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=264&amp;Niveau3=&amp;Niveau4=" => 	"le-centre-de-recherche-en-bref",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=265&amp;Niveau3=&amp;Niveau4=" => 	"plus-de-20-ans-dahistoire",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=266&amp;Niveau3=&amp;Niveau4=" => 	"mission-et-objectifs",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=267&amp;Niveau3=&amp;Niveau4=" => 	"la-direction",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=272&amp;Niveau3=&amp;Niveau4=" => 	"organisation",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=301&amp;Niveau3=&amp;Niveau4=" => 	"rapports-daactvites",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=269&amp;Niveau3=&amp;Niveau4=" => 	"le-csss-iugs-et-nous",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=270&amp;Niveau3=&amp;Niveau4=" => 	"launiversite-de-sherbrooke-et-nous",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=347&amp;Niveau3=&amp;Niveau4=" => 	"nos-partenaires",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=304&amp;Niveau3=&amp;Niveau4=" => 	"se-joindre-a-laequipe",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=288&amp;Niveau4=" => 	"pour-les-medias",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=288&amp;Niveau4=" => 	"pour-les-medias",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=273&amp;Niveau4=" => 	"actualites-et-communiques",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=275&amp;Niveau4=" => 	"rapports-daactivites",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=277&amp;Niveau4=" => 	"journal-encrage",
            "show_section.php?L=fr&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=303&amp;Niveau4=" => 	"autres-publications",
            "show_section.php?L=fr&amp;ParentID=259&amp;Niveau2=279&amp;Niveau3=&amp;Niveau4=" => 	"axe-clinique",
            "show_section.php?L=fr&amp;ParentID=259&amp;Niveau2=280&amp;Niveau3=&amp;Niveau4=" => 	"axe-mecanismes-biologiques-du-vieillissement",
            "show_section.php?L=fr&amp;ParentID=259&amp;Niveau2=281&amp;Niveau3=&amp;Niveau4=" => 	"axe-societepopulations-et-services",
            "show_section.php?L=fr&amp;ParentID=259&amp;Niveau2=297&amp;Niveau3=&amp;Niveau4=" => 	"laboratoires-et-equipements",
            "show_section.php?L=fr&amp;ParentID=260&amp;Niveau2=283&amp;Niveau3=&amp;Niveau4=" => 	"participer<br>à-une-étude-participer-a-une-etude",
            "show_section.php?L=fr&amp;ParentID=261&amp;Niveau2=284&amp;Niveau3=&amp;Niveau4=" => 	"evenements-du-centre-de-recherche",
            "show_section.php?L=fr&amp;ParentID=262&amp;Niveau2=285&amp;Niveau3=&amp;Niveau4=" => 	"centre-universitaire-de-formation-en-gerontologie-cufg",
            "show_section.php?L=fr&amp;ParentID=262&amp;Niveau2=286&amp;Niveau3=&amp;Niveau4=" => 	"bourses-et-subventions",
            "show_section.php?L=fr&amp;ParentID=262&amp;Niveau2=287&amp;Niveau3=&amp;Niveau4=" => 	"etudier",
            "show_section.php?L=fr&amp;ParentID=262&amp;Niveau2=346&amp;Niveau3=&amp;Niveau4=" => 	"recrutement",
            "show_section.php?L=fr&amp;ParentID=1&amp;Niveau2=7&amp;Niveau3=&amp;Niveau4=" => 	"mission",
            "show_section.php?L=fr&amp;ParentID=1&amp;Niveau2=8&amp;Niveau3=&amp;Niveau4=" => 	"conseil-d-administration",
            "show_section.php?L=fr&amp;ParentID=1&amp;Niveau2=10&amp;Niveau3=&amp;Niveau4=" => 	"historique",
            "show_section.php?L=fr&amp;ParentID=1&amp;Niveau2=9&amp;Niveau3=&amp;Niveau4=" => 	"un-nouveau-nom-et-un-nouveau-logo",
            "show_section.php?L=fr&amp;ParentID=1&amp;Niveau2=11&amp;Niveau3=&amp;Niveau4=" => 	"benevolat",
            "show_section.php?L=fr&amp;ParentID=2&amp;Niveau2=310&amp;Niveau3=&amp;Niveau4=" => 	"des-realisations-pour-tous",
            "show_section.php?L=fr&amp;ParentID=2&amp;Niveau2=12&amp;Niveau3=&amp;Niveau4=" => 	"achats-d-equipements",
            "show_section.php?L=fr&amp;ParentID=2&amp;Niveau2=13&amp;Niveau3=&amp;Niveau4=" => 	"amelioration-de-la-qualite-de-vie",
            "show_section.php?L=fr&amp;ParentID=2&amp;Niveau2=14&amp;Niveau3=&amp;Niveau4=" => 	"recherche",
            "show_section.php?L=fr&amp;ParentID=2&amp;Niveau2=15&amp;Niveau3=&amp;Niveau4=" => 	"reves-d-aines",
            "show_section.php?L=fr&amp;ParentID=2&amp;Niveau2=16&amp;Niveau3=&amp;Niveau4=" => 	"temoignages",
            "show_section.php?L=fr&amp;ParentID=3&amp;Niveau2=17&amp;Niveau3=&amp;Niveau4=" => 	"calendrier-des-activites",
            "show_section.php?L=fr&amp;ParentID=3&amp;Niveau2=387&amp;Niveau3=&amp;Niveau4=" => 	"classique-annuelle-de-golf",
            "show_section.php?L=fr&amp;ParentID=3&amp;Niveau2=389&amp;Niveau3=&amp;Niveau4=" => 	"coquetel-benefice-annuel",
            "show_section.php?L=fr&amp;ParentID=3&amp;Niveau2=388&amp;Niveau3=&amp;Niveau4=" => 	"dejeuners-annuels",
            "show_section.php?L=fr&amp;ParentID=3&amp;Niveau2=18&amp;Niveau3=&amp;Niveau4=" => 	"proposer-une-activite",
            "show_section.php?L=fr&amp;ParentID=4&amp;Niveau2=311&amp;Niveau3=&amp;Niveau4=" => 	"je-veux-faire-un-don",
            "show_section.php?L=fr&amp;ParentID=4&amp;Niveau2=19&amp;Niveau3=&amp;Niveau4=" => 	"10-bonnes-raisons-de-donner",
            "show_section.php?L=fr&amp;ParentID=4&amp;Niveau2=20&amp;Niveau3=&amp;Niveau4=" => 	"plusieurs-facons-de-donner",
            "show_section.php?L=fr&amp;ParentID=5&amp;Niveau2=348&amp;Niveau3=&amp;Niveau4=" => 	"actualites",
            "show_section.php?L=fr&amp;ParentID=5&amp;Niveau2=21&amp;Niveau3=&amp;Niveau4=" => 	"communiques-de-presse",
            "show_section.php?L=fr&amp;ParentID=5&amp;Niveau2=24&amp;Niveau3=&amp;Niveau4=" => 	"rapports-annuels",
            "show_section.php?L=fr&amp;ParentID=6&amp;Niveau2=307&amp;Niveau3=&amp;Niveau4=" => 	"partenaires",
            "show_section.php?L=fr&amp;ParentID=6&amp;Niveau2=26&amp;Niveau3=&amp;Niveau4=" => 	"donateurs-majeurs",
            "show_section.php?L=fr&ParentID=3&Niveau2=403&Niveau3=&Niveau4=" => 	"celebrity-match",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=&amp;Niveau4=" => 	"an-overview-of-the-csss-iugs",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=244&amp;Niveau4=" => 	"health-and-social-services",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=245&amp;Niveau4=" => 	"residential-and-long-term-care-centres",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=246&amp;Niveau4=" => 	"specialized-hospital-for-seniors",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=34&amp;Niveau3=395&amp;Niveau4=" => 	"prix-inspiration-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=35&amp;Niveau3=&amp;Niveau4=" => 	"mission-and-values",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=36&amp;Niveau3=&amp;Niveau4=" => 	"organization-plan",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=37&amp;Niveau3=&amp;Niveau4=" => 	"facilities",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=38&amp;Niveau3=&amp;Niveau4=" => 	"history",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=&amp;Niveau4=" => 	"volunteering",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=103&amp;Niveau4=" => 	"ten-ways-to-help-out",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=104&amp;Niveau4=" => 	"volunteers-in-action",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=39&amp;Niveau3=105&amp;Niveau4=" => 	"volunteer-support",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=&amp;Niveau4=" => 	"commitment-to-quality-and-security-services",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=41&amp;Niveau4=" => 	"code-of-ethics",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=371&amp;Niveau4=" => 	"double-identification-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=373&amp;Niveau4=" => 	"prevention-des-chutes-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=374&amp;Niveau4=" => 	"prevention-des-infections-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=40&amp;Niveau3=375&amp;Niveau4=" => 	"prevention-du-suicide-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=42&amp;Niveau3=&amp;Niveau4=" => 	"processing-of-complaints",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=42&amp;Niveau3=106&amp;Niveau4=" => 	"filing-of-complaints",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=42&amp;Niveau3=107&amp;Niveau4=" => 	"addressing-a-complaint",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=&amp;Niveau4=" => 	"committees-and-councils",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=108&amp;Niveau4=" => 	"corporations-of-the-csss-iugs",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=" => 	"board-of-directors",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=247" => 	"procedure-to-ask-a-question",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=248" => 	"calendar-of-meetings",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=109&amp;Niveau4=249" => 	"list-of-members",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=110&amp;Niveau4=" => 	"research-ethics-committees",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=330&amp;Niveau4=" => 	"clinical-ethics-committee",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=111&amp;Niveau4=" => 	"users-committee-and-residents-committee",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=112&amp;Niveau4=" => 	"council-of-nurses",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=113&amp;Niveau4=" => 	"council-of-nursing-assistants",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=114&amp;Niveau4=" => 	"council-of-physicians-dentists-and-pharmacists",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=116&amp;Niveau4=" => 	"multidisciplinary-council",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=117&amp;Niveau4=" => 	"paraprofessional-council",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=43&amp;Niveau3=118&amp;Niveau4=" => 	"council-of-midwives",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=&amp;Niveau4=" => 	"local-services-network-and-clinical-project",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=119&amp;Niveau4=" => 	"clinical-project-of-the-local-services-network",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=120&amp;Niveau4=" => 	"council-of-partners",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=44&amp;Niveau3=121&amp;Niveau4=" => 	"clinical-project-steering-committee",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=&amp;Niveau4=" => 	"our-partners",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=123&amp;Niveau4=" => 	"chus-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=316&amp;Niveau4=" => 	"sherbrooke-health-expertise-centre",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=124&amp;Niveau4=" => 	"socio-economic-actors",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=125&amp;Niveau4=" => 	"academic-environment",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=126&amp;Niveau4=" => 	"university-environment-and-physicians",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=127&amp;Niveau4=" => 	"municipalities",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=128&amp;Niveau4=" => 	"observatories",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=" => 	"observatoire-quebecois-des-rls-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=327" => 	"oqrls-newsletters",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=328" => 	"initiatives-identification-form",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=376" => 	"initiatives-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=377" => 	"partners",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=335&amp;Niveau4=381" => 	"articles-et-presentations-de-l-oqrls-en",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=129&amp;Niveau4=" => 	"health-and-social-services-network",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=130&amp;Niveau4=" => 	"community-resources",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=45&amp;Niveau3=309&amp;Niveau4=" => 	"other-partners",
            "show_section.php?L=en&amp;ParentID=27&amp;Niveau2=46&amp;Niveau3=&amp;Niveau4=" => 	"calendar-of-events",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=329&amp;Niveau3=&amp;Niveau4=" => 	"schedule",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=&amp;Niveau4=" => 	"childhood-youth-family",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=131" => 	"parental-support-for-children-aged-0-5-years",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=131" => 	"parental-support-for-children-aged-0-5-years",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=132" => 	"centre-de-maternite-maternity-centre?midwife-services",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=80&amp;Niveau4=133" => 	"family-medicine-groups",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=134" => 	"the-first-days-at-home",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=134" => 	"the-first-days-at-home",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=135" => 	"bebe-trucs-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=81&amp;Niveau4=136" => 	"breastfeeding-and-nursing",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=138" => 	"Ecole-en-sante-healthy-schools",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=138" => 	"Ecole-en-sante-healthy-schools",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=139" => 	"fluppy-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=140" => 	"dental-health",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=137&amp;Niveau4=141" => 	"school-nurse",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=83&amp;Niveau4=" => 	"childhood-youth-family-centre-d'enseignement-sur-l'asthme-asthma-education-centre-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=84&amp;Niveau4=" => 	"youth-clinic",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=85&amp;Niveau4=" => 	"learning-disabilities-and-autism-spectrum-disorders",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=86&amp;Niveau4=" => 	"childhood-youth-family-addictions-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=142&amp;Niveau4=" => 	"family-and-social-adaptation-problems",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=87&amp;Niveau4=" => 	"speech-language-pathology",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=87&amp;Niveau4=143" => 	"hanen-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=89&amp;Niveau4=" => 	"childhood-youth-family-mental-health-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=88&amp;Niveau4=144" => 	"health-and-social-services-auxiliary",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=88&amp;Niveau4=144" => 	"health-and-social-services-auxiliary",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=88&amp;Niveau4=145" => 	"financial-assistance",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=47&amp;Niveau3=90&amp;Niveau4=" => 	"childhood-youth-family-vaccination-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=&amp;Niveau4=" => 	"adults",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=91&amp;Niveau4=" => 	"help-and-psychological-support",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=93&amp;Niveau4=" => 	"adults-addictions-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=94&amp;Niveau4=" => 	"homelessness",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=95&amp;Niveau4=" => 	"oncology",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=146" => 	"Healthy-lifestyles-0-5-30",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=146" => 	"Healthy-lifestyles-0-5-30",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=147" => 	"stop-smoking-centre",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=92" => 	"chronic-disease-prevention-and-management-centre-d'enseignement-sur-l'asthme-asthma-education-centre-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=148" => 	"chronic-pulmonary-diseases",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=149" => 	"cardiac-rehabilitation",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=96&amp;Niveau4=315" => 	"diabetes",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=97&amp;Niveau4=" => 	"adults-mental-health-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=98&amp;Niveau4=" => 	"housing-resources",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=99&amp;Niveau4=" => 	"nursing",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=100&amp;Niveau4=" => 	"home-care-services",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=48&amp;Niveau3=101&amp;Niveau4=" => 	"adults-vaccination-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=&amp;Niveau4=" => 	"seniors-and-persons-in-loss-of-autonomy",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=102&amp;Niveau4=" => 	"housing",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=102&amp;Niveau4=154" => 	"other-housing-resources",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=155&amp;Niveau4=" => 	"home-support",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=156&amp;Niveau4=" => 	"day-centre",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=157&amp;Niveau4=" => 	"outpatient-clinics",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=167&amp;Niveau4=" => 	"psychiatric-gerontology",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=168&amp;Niveau4=" => 	"day-hospital",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=169&amp;Niveau4=" => 	"Short-term-hospitalization-short-term-geriatric-unit",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=170&amp;Niveau4=" => 	"fall-prevention",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=171&amp;Niveau4=" => 	"Rehabilitation-intensive-functional-rehabilitation-unit",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=49&amp;Niveau3=173&amp;Niveau4=" => 	"seniors-and-persons-in-loss-of-autonomy-vaccination-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=322&amp;Niveau3=&amp;Niveau4=" => 	"community-action",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=&amp;Niveau4=" => 	"international-traveller's-clinic",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=174&amp;Niveau4=" => 	"services-offered",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=338&amp;Niveau4=" => 	"vaccines-offered",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=175&amp;Niveau4=" => 	"fees",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=177&amp;Niveau4=" => 	"international-traveller-s-clinic-useful-links-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=339&amp;Niveau4=" => 	"operating-hours",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=50&amp;Niveau3=340&amp;Niveau4=" => 	"contact-us",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=166&amp;Niveau3=&amp;Niveau4=" => 	"physical-impairment",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=51&amp;Niveau3=&amp;Niveau4=" => 	"services-addictions-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=52&amp;Niveau3=&amp;Niveau4=" => 	"sexually-transmitted-and-blood-borne-infections",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=53&amp;Niveau3=&amp;Niveau4=" => 	"info-sante-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=178&amp;Niveau4=" => 	"do-you-need-a-family-physician",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=178&amp;Niveau4=" => 	"do-you-need-a-family-physician",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=361&amp;Niveau4=" => 	"csss-iugs-family-physician-access-registry",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=54&amp;Niveau3=179&amp;Niveau4=" => 	"Gmf-des-deux-rives-and-gmf-des-grandes-fourches-family-medicine-groups",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=172&amp;Niveau3=&amp;Niveau4=" => 	"palliative-care",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=55&amp;Niveau3=&amp;Niveau4=" => 	"urgence-detresse-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=56&amp;Niveau3=&amp;Niveau4=" => 	"services-vaccination-en",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=56&amp;Niveau3=344&amp;Niveau4=" => 	"seasonal-flu-vaccination",
            "show_section.php?L=en&amp;ParentID=28&amp;Niveau2=392&amp;Niveau3=&amp;Niveau4=" => 	"Referral-forms-reserved-for-professionals",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=&amp;Niveau4=" => 	"a-wide-range-of-career-opportunities-await-you",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=188&amp;Niveau4=" => 	"nursing-personnel",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=189&amp;Niveau4=" => 	"beneficiary-attendants-and-health-care-assistants",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=190&amp;Niveau4=" => 	"health-and-social-services-technicians-and-professionals",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=200&amp;Niveau4=" => 	"trades-personnel",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=198&amp;Niveau4=" => 	"other-professionals-and-technicians",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=202&amp;Niveau4=" => 	"adminisrative-personnel",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=196&amp;Niveau4=" => 	"research-personnel",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=191&amp;Niveau4=" => 	"managerial-and-supervisory-personnel",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=256&amp;Niveau3=204&amp;Niveau4=" => 	"summer-jobs",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=364&amp;Niveau4=" => 	"job-opportunities",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=364&amp;Niveau4=" => 	"job-opportunities",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=362&amp;Niveau4=" => 	"job-alert",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=366&amp;Niveau4=" => 	"spontaneous-applications",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=367&amp;Niveau4=" => 	"external-recruiters",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=368&amp;Niveau4=" => 	"recruitment-activities",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=363&amp;Niveau3=369&amp;Niveau4=" => 	"update-your-profile",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=&amp;Niveau4=" => 	"advantageous-working-conditions",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=185&amp;Niveau4=" => 	"work-life-balance",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=186&amp;Niveau4=" => 	"skills-and-competency-development-matters-to-us",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=58&amp;Niveau3=187&amp;Niveau4=" => 	"your-health-is-our-inspiration-at-the-csss-iugs",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=&amp;Niveau4=" => 	"the-csss-iugs--a-first-class-employer",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=181&amp;Niveau4=" => 	"sustainable-development-for-a-greener-environment",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=183&amp;Niveau4=" => 	"technologies-which-improve-care-and-services",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=57&amp;Niveau3=184&amp;Niveau4=" => 	"discover-life-in-sherbrooke",
            "show_section.php?L=en&amp;ParentID=29&amp;Niveau2=390&amp;Niveau3=&amp;Niveau4=" => 	"the-csss-iugs-in-a-few-words",
            "show_section.php?L=en&amp;ParentID=30&amp;Niveau2=306&amp;Niveau3=&amp;Niveau4=" => 	"vitae-foundation-vitae-foundation-en",
            "show_section.php?L=en&ParentID=31&Niveau2=380&Niveau3=&Niveau4=" => 	"acces-a-l'information-en",
            "show_section.php?L=en&ParentID=31&Niveau2=62&Niveau3=&Niveau4=" => 	"current-events",
            "show_section.php?L=en&ParentID=31&Niveau2=64&Niveau3=&Niveau4=" => 	"press-releases",
            "show_section.php?L=en&ParentID=31&Niveau2=63&Niveau3=&Niveau4=" => 	"health-bulletins",
            "show_section.php?L=en&ParentID=31&Niveau2=65&Niveau3=&Niveau4=" => 	"media-representatives",
            "show_section.php?L=en&ParentID=31&Niveau2=66&Niveau3=&Niveau4=" => 	"health--a-shared-passion",
            "show_section.php?L=en&ParentID=31&Niveau2=67&Niveau3=&Niveau4=" => 	"strategic-plan",
            "show_section.php?L=en&ParentID=31&Niveau2=68&Niveau3=&Niveau4=" => 	"annual-reports",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=&Niveau4=" => 	"library",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=206&Niveau4=" => 	"library-services-en",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=207&Niveau4=" => 	"policies-rules-and-regulations",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=208&Niveau4=" => 	"cameleon-catalog",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=209&Niveau4=" => 	"new-acquisitions",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=210&Niveau4=" => 	"collections-en",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=211&Niveau4=" => 	"periodicals",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=212&Niveau4=" => 	"electronic-resources",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=213&Niveau4=" => 	"documents-on-reserve-for-students",
            "show_section.php?L=en&ParentID=31&Niveau2=69&Niveau3=214&Niveau4=" => 	"library-useful-links-en",
            "show_section.php?L=en&ParentID=31&Niveau2=70&Niveau3=&Niveau4=" => 	"media-and-documentation-publications-en",
            "show_section.php?L=en&ParentID=31&Niveau2=70&Niveau3=384&Niveau4=" => 	"publications-officielles-en",
            "show_section.php?L=en&ParentID=31&Niveau2=70&Niveau3=372&Niveau4=" => 	"depliants-en",
            "show_section.php?L=en&ParentID=31&Niveau2=70&Niveau3=383&Niveau4=" => 	"autres-diffusions-en",
            "show_section.php?L=en&ParentID=31&Niveau2=323&Niveau3=&Niveau4=" => 	"collective-prescriptions",
            "show_section.php?L=en&ParentID=31&Niveau2=393&Niveau3=&Niveau4=" => 	"prase-en",
            "show_section.php?L=en&amp;ParentID=32&amp;Niveau2=252&amp;Niveau3=&amp;Niveau4=" => 	"education-at-the-csss-iugs",
            "show_section.php?L=en&amp;ParentID=32&amp;Niveau2=252&amp;Niveau3=337&amp;Niveau4=" => 	"umf-estrie-en",
            "show_section.php?L=en&amp;ParentID=32&amp;Niveau2=72&amp;Niveau3=&amp;Niveau4=" => 	"students",
            "show_section.php?L=en&amp;ParentID=32&amp;Niveau2=72&amp;Niveau3=253&amp;Niveau4=" => 	"welcoming-students",
            "show_section.php?L=en&amp;ParentID=32&amp;Niveau2=74&amp;Niveau3=&amp;Niveau4=" => 	"academic-institutions",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=75&amp;Niveau3=&amp;Niveau4=" => 	"research-centre-on-aging",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=&amp;Niveau4=" => 	"social-research-at-the-affiliated-university-centre",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=" => 	"research-axes",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=217" => 	"people-in-precarious-situations",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=218" => 	"research-axes-mental-health-en",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=216&amp;Niveau4=219" => 	"community-development",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=220&amp;Niveau4=" => 	"neighbourhood-interventions",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=331&amp;Niveau4=332" => 	"research-papers",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=331&amp;Niveau4=332" => 	"research-papers",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=331&amp;Niveau4=333" => 	"newsletters",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=336&amp;Niveau4=" => 	"events",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=76&amp;Niveau3=334&amp;Niveau4=" => 	"desjardins-cau-research-grants",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=77&amp;Niveau3=&amp;Niveau4=" => 	"other-projects",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=77&amp;Niveau3=221&amp;Niveau4=" => 	"current-research",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=78&amp;Niveau3=&amp;Niveau4=" => 	"research-ethics",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=396&amp;Niveau3=&amp;Niveau4=" => 	"nagano-demandes-d-evaluation-de-projet-en",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=396&amp;Niveau3=313&amp;Niveau4=" => 	"forms-and-guides",
            "show_section.php?L=en&amp;ParentID=33&amp;Niveau2=396&amp;Niveau3=386&amp;Niveau4=" => 	"politiques-et-procedures-en",
            "show_section.php?L=en&amp;ParentID=258&amp;Niveau2=278&amp;Niveau3=&amp;Niveau4=" => 	"researchers-researchers-en",
            "show_section.php?L=en&amp;ParentID=258&amp;Niveau2=302&amp;Niveau3=300&amp;Niveau4=" => 	"scientific-publications",
            "show_section.php?L=en&amp;ParentID=258&amp;Niveau2=302&amp;Niveau3=300&amp;Niveau4=" => 	"scientific-publications",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=264&amp;Niveau3=&amp;Niveau4=" => 	"summary-of-the-research-centre-on-aging",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=265&amp;Niveau3=&amp;Niveau4=" => 	"more-than-20-years-of-history",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=266&amp;Niveau3=&amp;Niveau4=" => 	"mission-and-objectives",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=267&amp;Niveau3=&amp;Niveau4=" => 	"board-of-director",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=272&amp;Niveau3=&amp;Niveau4=" => 	"organization",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=301&amp;Niveau3=&amp;Niveau4=" => 	"research-center-on-aging-activities-reports-en",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=269&amp;Niveau3=&amp;Niveau4=" => 	"the-csss-iugs-and-us",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=270&amp;Niveau3=&amp;Niveau4=" => 	"the-universite-de-sherbrooke-and-us",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=347&amp;Niveau3=&amp;Niveau4=" => 	"nos-partenaires-en",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=304&amp;Niveau3=&amp;Niveau4=" => 	"join-our-team",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=288&amp;Niveau4=" => 	"media",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=288&amp;Niveau4=" => 	"media",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=273&amp;Niveau4=" => 	"current-events-and-news-releases",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=275&amp;Niveau4=" => 	"documentation-and-media-centre-activities-reports-en",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=277&amp;Niveau4=" => 	"encrage-journal",
            "show_section.php?L=en&amp;ParentID=257&amp;Niveau2=271&amp;Niveau3=303&amp;Niveau4=" => 	"other-publications",
            "show_section.php?L=en&amp;ParentID=259&amp;Niveau2=279&amp;Niveau3=&amp;Niveau4=" => 	"clinical-axis",
            "show_section.php?L=en&amp;ParentID=259&amp;Niveau2=280&amp;Niveau3=&amp;Niveau4=" => 	"biological-mechanisms-of-aging-axis",
            "show_section.php?L=en&amp;ParentID=259&amp;Niveau2=281&amp;Niveau3=&amp;Niveau4=" => 	"societypopulations-and-services-axis",
            "show_section.php?L=en&amp;ParentID=259&amp;Niveau2=297&amp;Niveau3=&amp;Niveau4=" => 	"laboratories-and-equipment",
            "show_section.php?L=en&amp;ParentID=260&amp;Niveau2=283&amp;Niveau3=&amp;Niveau4=" => 	"participate<br>in-a-study-participate-in-a-study-en",
            "show_section.php?L=en&amp;ParentID=261&amp;Niveau2=284&amp;Niveau3=&amp;Niveau4=" => 	"activities-of-the-research-centre-on-aging",
            "show_section.php?L=en&amp;ParentID=262&amp;Niveau2=285&amp;Niveau3=&amp;Niveau4=" => 	"centre-universitaire-de-formation-en-gerontologie-cufg-en",
            "show_section.php?L=en&amp;ParentID=262&amp;Niveau2=286&amp;Niveau3=&amp;Niveau4=" => 	"bursariesgrants-and-scholarships",
            "show_section.php?L=en&amp;ParentID=262&amp;Niveau2=287&amp;Niveau3=&amp;Niveau4=" => 	"study",
            "show_section.php?L=en&amp;ParentID=262&amp;Niveau2=346&amp;Niveau3=&amp;Niveau4=" => 	"recruitment",
            "show_section.php?L=en&amp;ParentID=1&amp;Niveau2=7&amp;Niveau3=&amp;Niveau4=" => 	"mission-en",
            "show_section.php?L=en&amp;ParentID=1&amp;Niveau2=8&amp;Niveau3=&amp;Niveau4=" => 	"board-of-directors",
            "show_section.php?L=en&amp;ParentID=1&amp;Niveau2=10&amp;Niveau3=&amp;Niveau4=" => 	"history",
            "show_section.php?L=en&amp;ParentID=1&amp;Niveau2=9&amp;Niveau3=&amp;Niveau4=" => 	"a-new-name-and-a-new-logo",
            "show_section.php?L=en&amp;ParentID=1&amp;Niveau2=11&amp;Niveau3=&amp;Niveau4=" => 	"volunteering",
            "show_section.php?L=en&amp;ParentID=2&amp;Niveau2=310&amp;Niveau3=&amp;Niveau4=" => 	"accomplishments-for-everyone",
            "show_section.php?L=en&amp;ParentID=2&amp;Niveau2=12&amp;Niveau3=&amp;Niveau4=" => 	"equipment-purchases",
            "show_section.php?L=en&amp;ParentID=2&amp;Niveau2=13&amp;Niveau3=&amp;Niveau4=" => 	"improving-quality-of-life",
            "show_section.php?L=en&amp;ParentID=2&amp;Niveau2=14&amp;Niveau3=&amp;Niveau4=" => 	"research",
            "show_section.php?L=en&amp;ParentID=2&amp;Niveau2=15&amp;Niveau3=&amp;Niveau4=" => 	"elder-wish",
            "show_section.php?L=en&amp;ParentID=2&amp;Niveau2=16&amp;Niveau3=&amp;Niveau4=" => 	"user-impressions",
            "show_section.php?L=en&amp;ParentID=3&amp;Niveau2=17&amp;Niveau3=&amp;Niveau4=" => 	"calendar-of-activities",
            "show_section.php?L=en&amp;ParentID=3&amp;Niveau2=387&amp;Niveau3=&amp;Niveau4=" => 	"annual-golf-classic",
            "show_section.php?L=en&amp;ParentID=3&amp;Niveau2=389&amp;Niveau3=&amp;Niveau4=" => 	"cocktail-benefit",
            "show_section.php?L=en&amp;ParentID=3&amp;Niveau2=388&amp;Niveau3=&amp;Niveau4=" => 	"annual-breakfast",
            "show_section.php?L=en&amp;ParentID=3&amp;Niveau2=18&amp;Niveau3=&amp;Niveau4=" => 	"proposing-an-activity",
            "show_section.php?L=en&amp;ParentID=4&amp;Niveau2=311&amp;Niveau3=&amp;Niveau4=" => 	"i-want-to-make-a-donation",
            "show_section.php?L=en&amp;ParentID=4&amp;Niveau2=19&amp;Niveau3=&amp;Niveau4=" => 	"10-good-reasons-to-give",
            "show_section.php?L=en&amp;ParentID=4&amp;Niveau2=20&amp;Niveau3=&amp;Niveau4=" => 	"ways-to-donate",
            "show_section.php?L=en&amp;ParentID=5&amp;Niveau2=348&amp;Niveau3=&amp;Niveau4=" => 	"current-evens",
            "show_section.php?L=en&amp;ParentID=5&amp;Niveau2=21&amp;Niveau3=&amp;Niveau4=" => 	"press-releases",
            "show_section.php?L=en&amp;ParentID=5&amp;Niveau2=24&amp;Niveau3=&amp;Niveau4=" => 	"annual-reports",
            "show_section.php?L=en&amp;ParentID=6&amp;Niveau2=307&amp;Niveau3=&amp;Niveau4=" => 	"our-partners",
            "show_section.php?L=en&amp;ParentID=6&amp;Niveau2=26&amp;Niveau3=&amp;Niveau4=" => 	"major-donors",
            "show_section.php?L=en&ParentID=3&Niveau2=403&Niveau3=&Niveau4=" => 	"celebrity-match"
            );
        $replaceSrc = array(
            '/cdrv/from_fckeditor/images/' => '/cdrv/data/images/content/',
            '/fondation/from_fckeditor/images/' => '/fondation/data/images/content/',
            '/images/from_fckeditor/images/' => '/c3s/data/images/content/',
        );

        $oText = new TextObject();
        $data = $oText->getAll();
//        $data = array(0=>$oText->populate(208, 1));
//        $tmp2 = $tmp = array();
        foreach ($data as $key => $text)
        {
            $online = $text['TD_OnlineText'];
            $draft = $text['TD_DraftText'];

//            if (preg_match('/show_section/', $online) || preg_match('/http:\/\//', $online))

            if (preg_match('/href=/', $online))
            {
                foreach ($replace as $key => $value)
                {
                    if (preg_match('/http:\/\//', $key))
                        $online = str_replace($key, $value, $online);
                    else
                        $online = str_replace($key, '/' . $value, $online);
                }
            }
            if (preg_match('/href=/', $draft))
            {
                foreach ($replace as $key => $value)
                {
                    if (preg_match('/http:\/\//', $key))
                        $draft = str_replace($key, $value, $draft);
                    else
                        $draft = str_replace($key, '/' . $value, $draft);
                }
            }
            if (preg_match('/src=/', $online))
            {
                foreach ($replaceSrc as $key => $value)
                    $online = str_replace($key, $value, $online);
            }
            if (preg_match('/src=/', $draft))
            {
                foreach ($replaceSrc as $key => $value)
                    $draft = str_replace($key, $value, $draft);
            }

            $text['TD_OnlineText'] = $online;
            $text['TD_DraftText'] = $draft;
            $id = $text['TD_ID'];
            $langId = $text['TD_LanguageID'];

            $oText->save($id, $text, $langId);
        }
        exit;
    }
}
?>