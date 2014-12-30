<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StatisticController
 *
 * @author soaser
 */
class Newsletter_StatisticController extends Cible_Extranet_Controller_Module_Action
{
    protected $_moduleID = 8;
    protected $_typesNoCount = array('sendTo', 'subscribe', 'unsubscribe');
    public $nlObject;


    public function init()
    {
        parent::init();
        $this->view->language = Cible_FunctionsGeneral::getLanguageSuffix($this->_currentInterfaceLanguage);
        $this->view->headLink()->appendStylesheet($this->view->locateFile('newsletterStats.css'));
        $this->view->headScript()->appendFile($this->view->locateFile('jquery.dataTables.min.js', 'datatable'));
        $this->view->headScript()->appendFile($this->view->locateFile('dataTables.fourButtonNavigation.js', 'datatable/plugins'));
        $this->view->headScript()->appendFile($this->view->locateFile('statisticAction.js'));

        $this->nlObject = new NewsletterLog(array('moduleId' => $this->_moduleID));
    }
    public function indexAction()
    {
        $data   = array();
        $oNlLog = $this->nlObject;

        $releaseId = $this->_getParam('releaseId');

        if ($releaseId)
            $oNlLog->setReleaseId($releaseId);

        $data['subscription'] = $oNlLog->countSubscriptions();
        $data['unsubscribe']  = $oNlLog->countUnsubscribe();
        $data['latestSend']   = $oNlLog->getLastSending();
        $data['newsletters']  = $oNlLog->getNewslettersList();
        $data['categories']   = $oNlLog->getCategoriesList();

        $this->view->data = $data;

        $consultation = $oNlLog->getReleaseLog();
        $this->view->assign('consultation', $consultation);
        $html = $this->releaseLog($consultation, $data['newsletters']);

        if ($this->_isXmlHttpRequest)
            return $html;

        $this->view->view1 = $html;

    }

    public function ajaxAction()
    {
        $this->disableLayout();
        $this->disableView();
        $params = $this->_getAllParams();
        $report = $params['report'];

        if (!empty($params['releaseId']))
        {
            $releaseId = ($params['releaseId'] == 'undefined' || $params['releaseId'] == 'empty') ? 0 : $params['releaseId'];
            $this->nlObject->setReleaseId($releaseId);
        }
        if (!empty ($params['startDate']))
            $this->nlObject->setDateStart($params['startDate']);
        if (!empty ($params['endDate']))
            $this->nlObject->setDateEnd($params['endDate']);

        switch ($report)
        {
            case 'articles':
                $html = $this->getArticles($params['releaseId']);
                echo json_encode($html);
                break;
            case 'releases':
                $html = $this->indexAction();
                echo ($html);
                break;
            case 'subscribe':
                $html = $this->subscriptionLog(1);
                echo $html;
                break;
            case 'unsubscribe':
                if (!empty($params['categoryId']))
                    $this->nlObject->setCategoryId($params['categoryId']);
                $html = $this->subscriptionLog(2);
                echo ($html);
                break;
            case 'users':
                $type = $this->_getParam('viewType');
                if (!empty($params['categoryId']))
                    $this->nlObject->setCategoryId($params['categoryId']);
                if ($this->nlObject->getReleaseId() == 0 && $type == 'unsubscribe')
                    $html = $this->subscriptionLog(2);
                else
                    $html = $this->getUsers();

                echo $html;
                break;

            default:
                break;
        }
    }

    public function releaseLog($data = array(), $releases = array())
    {
        $infos = array();
        foreach ($data as $releaseId => $logData)
        {
            if (!empty($releaseId) && array_key_exists($releaseId, $releases))
            $infos[$releaseId] = array_merge($releases[$releaseId], $logData);
        }

        $this->view->assign('consultation', $infos);
        $html = $this->view->render('statistic/release-log.phtml');

        return $html;
    }

    public function getArticles()
    {
        $data = $this->nlObject->getArticlesData();

        $this->view->assign('articles', $data);
        $html = $this->view->render('statistic/articles.phtml');

        return $html;
    }

    public function subscriptionLog($type = 1, $releaseId = null, $dates = array())
    {
        switch ($type)
        {
            case 1:
                $data = $this->nlObject->getSubscriptionLog();
                $this->view->assign('data', $data);
                $html = $this->view->render('statistic/subscribe.phtml');
                break;
            case 2:
                $infos = array();
                $data = $this->nlObject->getUnSubscribeLog();
                $newsletters = $this->nlObject->getNewslettersList();

                foreach ($data as $releaseId => $logData)
                {
                    if ($releaseId > 0)
                        $infos[$releaseId] = array_merge($newsletters[$releaseId], $logData);
                    else
                        $infos[$releaseId] =  $logData;
                }

                $this->view->assign('data', $infos);

                if ($this->nlObject->getReleaseId() === 0)
                    $html = $this->view->render('statistic/reasons-list.phtml');
                else
                    $html = $this->view->render('statistic/unsubscribe.phtml');
                break;

            default:
                break;
        }

        return $html;
    }

    public function getUsers()
    {
        $data = array();
        $article = $this->_getParam('articleId');
        $type    = $this->_getParam('viewType');
        $this->nlObject->setArticleId($article);
        $data = $this->nlObject->getViewersList($type);
        $data['viewType']  = '';
        if (in_array($type, $this->_typesNoCount))
            $data['viewType']  = 'noCount';

        $this->view->assign('data', $data);
        $html = $this->view->render('statistic/viewers-list.phtml');

        return $html;
    }

    public function addAction(){}
    public function editAction(){}
    public function deleteAction(){}

}