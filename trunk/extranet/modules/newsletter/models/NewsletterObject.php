<?php
/**
 * Module Catalog
 * Management of the Items.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: NewsletterObject.php 1633 2014-07-04 17:18:24Z ssoares $id
 */

/**
 * Manage data from items table.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: NewsletterObject.php 1633 2014-07-04 17:18:24Z ssoares $id
 */
class NewsletterObject extends DataObject
{
    protected $_dataClass   = 'NewsletterReleases';
    protected $_dataId      = '';
    protected $_constraint      = '';
    protected $_foreignKey      = '';
    protected $_filterDates = array();

    /**
     * Builds folder to manage images and files according to the current website.
     *
     * @param string  $module The current module name.
     * @param string  $path Path relative to the current site.
     *
     * @return void
     */
    public function buildBasicsFolders($module, $path)
    {
        $imgPath = $path . '/data/images/' . $module ;
        if (!is_dir($imgPath))
        {
            mkdir ($imgPath);
            mkdir ($imgPath . '/headerCourriel' );
            mkdir ($imgPath . '/background/' );
            mkdir ($imgPath . '/background/tmp' );
            mkdir ($imgPath . '/tmp' );
        }
    }

    public function setIndexationData()
    {
        $newsletterSelect = new NewsletterReleases();
        $select = $newsletterSelect->select()
            ->from('Newsletter_Releases', array(
                'ID' => 'NR_ID',
                'LanguageID' => 'NR_LanguageID',
                'Category' => 'NR_CategoryID',
                'Title' => 'NR_Title',
                'Date' => 'NR_Date',
                'ValUrl' => 'NR_ValUrl'
            ))
            ->where('NR_Online = 1')
            ->where('NR_Date <= ?', date('Y-m-d', time()));

        $newsletterData = $newsletterSelect->fetchAll($select)->toArray();
        $cpt = count($newsletterData);
        foreach ($newsletterData as $data)
        {
            $indexData['action'] = "add";
            $indexData['pageID'] = $data['Category'];
            $indexData['moduleID'] = 8;
            $indexData['contentID'] = $data['ID'];
            $indexData['languageID'] = $data['LanguageID'];
            $indexData['title'] = $data['Title'];
            $indexData['text'] = '';
            $indexData['link'] = $data['Date'] . '/' . $data['ValUrl'];
            $indexData['object'] = get_class();
            $indexData['contents'] = $data['Title'];

            Cible_FunctionsIndexation::indexation($indexData);
            $articlesSelect = new NewsletterArticles();
            $select = $articlesSelect->select()
                ->from('Newsletter_Articles', array(
                    'ID' => 'NA_ID',
                    'Title' => 'NA_Title',
                    'Resume' => 'NA_Resume',
                    'Text' => 'NA_Text',
                    'ValUrl' => 'NA_ValUrl'
                    ))
                ->where('NA_ReleaseID = ?', $data['ID']);
            $articlesData = $articlesSelect->fetchAll($select);
            foreach ($articlesData as $article)
            {
                $indexData['action'] = "add";
                $indexData['pageID'] = $data['Category'];
                $indexData['moduleID'] = 8;
                $indexData['contentID'] = $data['ID'];
                $indexData['languageID'] = $data['LanguageID'];
                $indexData['title'] = $article['Title'];
                $indexData['text'] = '';
                $indexData['link'] = $indexData['link'] . '/' . $article['ValUrl'];
                $indexData['object'] = get_class();
                $indexData['contents'] = $article['Title'] . " "
                    . $article['Resume']
                    . " " . $article['Text'];

                Cible_FunctionsIndexation::indexation($indexData);
            }
        }


        return $this;
    }

    public function getFilterDates()
    {
        if (empty($this->_filterDates)){
            $this->_filterDates = array(
                '-6m' => array(
                    'value' => 6,
                    'label' => Cible_Translation::getCibleText('form_enum_datelimit_lt6m'),
                    'filter' => ' >= curdate() - INTERVAL 6 MONTH'),
                '+6m' => array(
                    'value' => 6,
                    'label' => Cible_Translation::getCibleText('form_enum_datelimit_gt6m'),
                    'filter' => ' <= curdate() - INTERVAL 6 MONTH'
                ),
                '-2y' => array(
                    'value' => 2,
                    'label' => Cible_Translation::getCibleText('form_enum_datelimit_lt2y'),
                    'filter' => ' >= curdate() - INTERVAL 2 YEAR'),
                '+2y' => array(
                    'value' => 2,
                    'label' => Cible_Translation::getCibleText('form_enum_datelimit_gt2y'),
                    'filter' => ' <= curdate() - INTERVAL 2 YEAR'),
                '-3y' => array(
                    'value' => 3,
                    'label' => Cible_Translation::getCibleText('form_enum_datelimit_lt3y'),
                    'filter' => ' >= curdate() - INTERVAL 3 YEAR'),
                '+3y' => array(
                    'value' => 3,
                    'label' => Cible_Translation::getCibleText('form_enum_datelimit_gt3y'),
                    'filter' => ' <= curdate() - INTERVAL 3 YEAR'),
            );
        }
        return $this->_filterDates;
    }

    public function setFilterDates($filterDates)
    {
        $this->_filterDates = $filterDates;
        return $this;
    }
}