<?php

class Cible_View_Helper_SiteList extends Zend_View_Helper_Abstract
{

    protected $_config = null;
    protected $_id = 'sitelist';
    protected $_currentSite = null;
    protected $_frontOffice = false;
    protected $_getValues = false;
    protected $_getDomains = false;
    protected $_list = array();
    protected $_unique = 0;

    public function siteList($options = array())
    {
        $attribs = array(
            'class' => 'siteSwitch',
            );
        foreach ($options as $key => $value)
        {
            $property = '_' . $key;
            $this->$property = $value;
        }
        $this->_config = Zend_Registry::get('config');
        $this->_getDomains = false;
        if (isset($options['getDomains']) && $options['getDomains'])
            return $this->_getDomainsList();

        $this->_getValues = false;
        if (isset($options['getValues']) && $options['getValues'])
        {
            $this->_getValues = $options['getValues'];
            $this->setCurrentSite();
        }
        else
            $this->setCurrentSite();

        $siteList = $this->getSiteList();

        $select = $this->view->formSelect(
            $this->_id, $this->_currentSite, $attribs, $siteList);
        if ($this->_getValues)
            return $siteList;
        else
        {
            $isLoginPage = empty($this->view->user['EU_SiteAccess']);
            $dispCurrentLogo = $isLoginPage && count($siteList) > 1;
            if ($this->_unique > 1 || $dispCurrentLogo)
                return $select;
            else
                return '';
        }
    }

    public function setCurrentSite()
    {
        $session = new Zend_Session_Namespace(SESSIONNAME);
        if (!$this->_frontOffice)
        {
            $this->_currentSite = $this->view->user['EU_DefaultSite'];
        }
        else
        {
            foreach ($this->_config->multisite as $data)
            {
                if ((bool) $data->active)
                {
                    $name =  $data->name;
                    array_push($this->_list, $name);
                    $dbName = $this->_config->resources->multidb->$name->dbname;
                    $db = Zend_Registry::get('db');
                    $currentDbName = $db->fetchOne("select DATABASE();");
                    if ($dbName === $currentDbName)
                        $session->currentSite = $data->name;

                }
            }
            if (!empty($this->_list))
                Zend_Registry::set ('sitesList', $this->_list);
        }
        if (isset($session->currentSite))
            $this->_currentSite = $session->currentSite;

        Zend_Registry::set('currentSite', $this->_currentSite);

    }

    public function getSiteList()
    {
        $siteList = array();
        $sites = '';
        if (isset($this->view->user['EU_SiteAccess']) && !$this->_getValues)
        {
            if (empty($this->view->user['EU_SiteAccess']))
            {
                $this->_config = Zend_Registry::get('config');
                foreach ($this->_config->multisite as $data)
                {
                    if ((bool) $data->active)
                    $siteList[$data->name] = $this->view->getClientText('site_label_' . $data->name);
                }
            }
            else
            {
                $sites = explode('|', $this->view->user['EU_SiteAccess']);
                $this->_unique = count($sites);
                foreach ($sites as $data)
                    $siteList[$data] = $this->view->getClientText('site_label_' . $data);
            }
        }
        elseif($this->_frontOffice)
            $siteList = $this->_currentSite;
        else
        {
            $this->_config = Zend_Registry::get('config');
            foreach ($this->_config->multisite as $data)
            {
                if ((bool) $data->active)
                $siteList[$data->name] = $this->view->getClientText('site_label_' . $data->name);
            }
        }

        return $siteList;
    }

    private function _getDomainsList()
    {
        foreach ($this->_config->multisite as $data)
        {
            if ((bool) $data->active)
            {
                $site  = $data->name;
                $list[$site] = $this->_config->domainNames->$site;
            }
        }

        return $list;
    }

}