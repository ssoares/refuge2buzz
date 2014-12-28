<?php

    class CategoriesObject extends DataObject
    {
        protected $_dataClass = 'Categories';
        protected $_dataId = 'C_ID';
        protected $_dataColumns = array(
            'ModuleID' => 'C_ModuleID',
            'ShowInRss' => 'C_ShowInRss',
            'RssItemsCount' => 'C_RssItemsCount'
        );
        
        protected $_indexClass = 'CategoriesIndex';
        protected $_indexId = 'CI_CategoryID';
        protected $_indexLanguageId = 'CI_LanguageID';
        protected $_indexColumns = array(
            'Title' => 'CI_Title',
            'WordingShowAllRecords' => 'CI_WordingShowAllRecords',
            'CI_ReturnToList' => 'CI_ReturnToList'
        );
    }
    
?>
