<?php
class News_IndexController extends Cible_Controller_Action
{

    protected $_showBlockTitle = false;

    /**
     * Overwrite the function defined in the SiteMapInterface implement in Cible_Controller_Action
     *
     * This function return the sitemap specific for this module
     *
     * @access public
     *
     * @return a string containing xml sitemap
     */
    public function siteMapAction()
    {
        $newsRob = new NewsRobots();
        $dataXml = $newsRob->getXMLFile($this->_request->getParam('lang'));

        parent::siteMapAction($dataXml);
    }

    public function init()
    {
        parent::init();
        $this->setModuleId();
        $this->view->headLink()->offsetSetStylesheet($this->_moduleID, $this->view->locateFile('news.css'),'all');
        $this->view->headLink()->appendStylesheet($this->view->locateFile('news.css'),'all');
        $this->view->assign('otherData', false);

    }

    public function detailsAction()
    {
        $news = new NewsCollection($this->_blockId);
        $news->setBlock();
        $this->_testDuplicatedContent(true);
        if (!empty($this->_duplicateData))
            $news->setDb($this->_db)
            ->setDuplicateId ($this->_duplicateData['B_DuplicateId'])
            ->setFromSite ($this->_duplicateData['B_FromSite']);
        $id = 0;
        $titleUrl = Cible_FunctionsGeneral::getTitleFromPath($this->_request->getPathInfo());
        if ($titleUrl != "")
        {
            $id = $news->getIdByName($titleUrl);
        }
        $data = $news->getDetails($id);
        if (!empty($data))
        {
            $newsCategoryDetails = Cible_FunctionsCategories::getCategoryDetails($data[0]['ND_CategoryID']);
            $this->view->assign('newsCategoryDetails', $newsCategoryDetails);
        }
        else
        {
            $otherData = (bool)$this->_hasContent($news, $id);
            if ($otherData)
                $this->view->assign('otherData', true);
        }
        $this->_resetDefaultAdapter();
        $listall_page = $this->view->parentPage($this->_blockId);

        $this->view->assign('params', $news->getBlockParams());
        $this->view->assign('news', $data);

        $this->view->assign('listall_page', $listall_page);
    }

    public function homepagelistAction()
    {
        $news = new NewsCollection($this->_blockId);
        $news->setBlock();
        $listall_page = Cible_FunctionsCategories::getPagePerCategoryView($news->getBlockParam(1), 'listall', 2, null, true);
        $details_page = Cible_FunctionsCategories::getPagePerCategoryView($news->getBlockParam(1), 'detailswithpreviousnext', 2, null, true);
        $this->_testDuplicatedContent(true);
        if (!empty($this->_duplicateData))
            $news->setDb($this->_db)
            ->setDuplicateId ($this->_duplicateData['B_DuplicateId'])
            ->setFromSite ($this->_duplicateData['B_FromSite']);

        $data = $news->getList($news->getBlockParam('2'));
        if (empty($data))
        {
            $otherData = (bool)$this->_hasContent($news);
            if ($otherData)
                $this->view->assign('otherData', true);
        }
        $this->_resetDefaultAdapter();
        $this->view->assign('listall_page', $listall_page);
        $this->view->assign('details_page', $details_page);
        $this->view->assign('params', $news->getBlockParams());
        $this->view->assign('news', $data);
    }

    public function homepagelist1colAction()
    {
        $this->homepagelistAction();
    }

    public function listallAction()
    {
        $detailsPageView = 'details';
        $detailsPageWithPaginator = false;
        $pageDetails = Cible_FunctionsPages::getPageDetails(Zend_Registry::get('pageID'), $this->_getParam('lang'));
        $childPageDetails = Cible_FunctionsPages::findChildPage($pageDetails['P_ID'], $this->_getParam('lang'), 's');
        $oBlock = new BlocksObject();
        foreach($childPageDetails as $childPage)
        {
            $oBlock->setProperties(array('pageId' => $childPage['P_ID']));
            $detailsPageView = $oBlock->getViewByModuleID($this->_moduleID);
            if($detailsPageView == 'detailswithpreviousnext')
            {
                $detailsPageWithPaginator = true;
                break;
            }
        }
        /*if($detailsPageView == 'detailswithpreviousnext')
            $detailsPageWithPaginator = true;*/

        $newsObject = new NewsCollection($this->_blockId);
        $newsObject->setBlock();
        $details_page = Cible_FunctionsCategories::getPagePerCategoryView($newsObject->getBlockParam('1'), $detailsPageView);
        $this->view->assign('details_page', $details_page);
        $this->_testDuplicatedContent(true);
        if (!empty($this->_duplicateData))
            $newsObject->setDb($this->_db)
            ->setDuplicateId ($this->_duplicateData['B_DuplicateId'])
            ->setFromSite ($this->_duplicateData['B_FromSite']);

        $options = $this->_setFilter($newsObject);
        $news = $newsObject->getList(null, $options['filtre']);
        if (empty($news))
        {
            $otherData = (bool)$this->_hasContent($newsObject);
            if ($otherData)
                $this->view->assign('otherData', true);
        }
        $this->_resetDefaultAdapter();
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($news));
        $paginator->setItemCountPerPage($newsObject->getBlockParam('2'));
        $paginator->setCurrentPageNumber($this->_request->getParam('page'));
        $this->view->assign('params', $newsObject->getBlockParams());
        $this->view->assign('paginator', $paginator);
        $this->view->assign('detailsPageWithPaginator', $detailsPageWithPaginator);
    }

    public function detailswithpreviousnextAction()
    {
        $newsObject = new NewsCollection($this->_blockId);
        $newsObject->setBlock();
        $this->_testDuplicatedContent(true);
        if (!empty($this->_duplicateData))
            $newsObject->setDb($this->_db)
            ->setDuplicateId ($this->_duplicateData['B_DuplicateId'])
            ->setFromSite ($this->_duplicateData['B_FromSite']);

        $options = $this->_setFilter($newsObject);
        $news = $newsObject->getList(null, $options['filtre']);
        if (empty($news))
        {
            $otherData = (bool)$this->_hasContent($newsObject);
            if ($otherData)
                $this->view->assign('otherData', true);
        }

        $newsCategoryDetails = Cible_FunctionsCategories::getCategoryDetails($news[0]['ND_CategoryID']);
        $this->view->assign('newsCategoryDetails', $newsCategoryDetails);

        $this->_resetDefaultAdapter();
        $listall_page = $this->view->parentPage($this->_blockId);
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($news));
        //$paginator->setItemCountPerPage($newsObject->getBlockParam('2'));
        $paginator->setItemCountPerPage(1);
        $paginator->setCurrentPageNumber($this->_request->getParam('page'));
        $this->view->assign('params', $newsObject->getBlockParams());
        $this->view->assign('paginator', $paginator);
        $this->view->assign('listall_page', $listall_page);
    }

    public function listall2Action()
    {
        $this->listallAction();
    }

    public function listall3columnsAction()
    {
        $this->listallAction();
    }

    public function langswitchAction()
    {
        $this->disableView();
        $lang = $this->_getParam('lang');
        $url = $this->_getParam('url');
        $path = explode('/', $this->_request->getPathInfo());
        array_shift($path);
        array_shift($path);

        if (count($path) > 2)
        {
            $urltmp = str_replace($this->view->BaseUrl(), '', $url);
            $tmpArray = explode('/', $urltmp);
            $obj = new NewsCollection();
            $val = $obj->getIdByName($path[2]);

            if (!is_null($val))
            {
                $valUrl = $obj->getValUrl($val, $lang);
                if($valUrl!=""){
                    $tmpArray[4] = $valUrl;
                    $url = $this->view->BaseUrl() . implode('/', $tmpArray);
                }
            }
        }
        echo $url;
    }

    /**
     * Defines data and build the filter.
     *
     * @param NewsCollection $newsObject The collection data object.
     * @return array
     */
    private function _setFilter($newsObject)
    {
        $datesList = array();
        $options["filtre"] = '';
        $param = $newsObject->getBlockParam('7');
        if ($param)
        {
            $options["filtre"] = $this->_request->getParam('listeFiltre');
            $arraySelect = $newsObject->getFilterArchive();
            $date = 0;
            foreach ($arraySelect as $value)
            {
                if ($value['dates'] > $date)
                    $date = date('Y-m', strtotime($value['dates']));
                $year = date('Y', strtotime($value['dates']));
                $index = date('Y-m', strtotime($value['dates']));
                $valDt = Cible_FunctionsGeneral::dateToString($value['dates'], Cible_FunctionsGeneral::DATE_MONTH_YEAR);
                $datesList[$year][$index] = $valDt . ' ('. $value['nbNews'] .')';

            }

            $options['datesList'] = $datesList;
            if($options['filtre'] == "")
                $options['filtre'] = $date;

            $form = new FormFilterDate($options);
            $this->view->formSelect = $form;
            if($this->_request->isPost())
            {
                 $form->populate($this->_request->getPost());
            }
        }

        return $options;
    }

    private function _hasContent($obj, $id = null)
    {
        $nbValues = 0;
        $langs = Cible_FunctionsGeneral::getAllLanguage();
        $lang = $this->view->languageId;
        foreach ($langs as $lg)
        {
            if ($lg['L_ID'] != $lang)
            {
                $obj->setCurrentLang($lg['L_ID']);
                if (is_null($id))
                    $data = $obj->getList($obj->getBlockParam('2'));
                else
                    $data = $obj->getDetails($id);

                $nbValues += count($data);
            }
        }

        return $nbValues;
    }
}
