<?php

/**
 * LICENSE
 *
 * @category
 * @package

 * @license   Empty
 */

/**
 * Manage newsletters data.
 *
 * @category
 * @package

 * @license   Empty
 * @version   $Id: NewsletterObject.php 1473 2014-02-19 22:27:09Z ssoares $
 */
class NewsletterObject extends DataObject
{
    protected $_dataClass = 'NewsletterReleases';
    protected $_id;

    public function setId($id)
    {
        $this->_id = $id;
    }


    /**
     * Retrieve the model path to render the current release
     * @return array
     */
    public function getModel($modelId)
    {
        //echo $modelId;
        //exit;
        $data = array();
        $oModels = new NewsletterModelsObject();

        $lang = Zend_Registry::get('languageID');
        if (empty($modelId))
            $model = $oModels->getDefault();
        else
            $model = $oModels->getAll($lang, true, $modelId);

        $data = explode('/', $model[0]['NM_Directory']);
        array_pop($data);
        $data = implode('/', $data);

        return $data;
    }

    public function getIndexationData(array $result)
    {
        $newsData = parent::populate($result['contentID'], $result['languageID']);
        $linkToRelease = count(explode('/', $result['link'])) == 2 ? true : false;
        if ($linkToRelease)
        {
            $link = '/' . Cible_FunctionsCategories::getPagePerCategoryView($result['pageID'], 'details_release');
        }
        else
        {
            $link = '/' . Cible_FunctionsCategories::getPagePerCategoryView($result['pageID'], 'details_article');

        }

        if ($newsData['NR_Date'] <= date('Y-m-d') && $newsData['NR_Online'])
        {
            $link .= '/' . $result['link'];
            $result['link'] = $link;
            if (!$linkToRelease)
                $result['title'] = $newsData['NR_Title'];

        }

        return $result;
    }

}