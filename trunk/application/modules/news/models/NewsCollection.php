<?php

class NewsCollection// extends objectsCollection
{

    protected $_db;
    protected $_current_lang;
    protected $_blockID;
    protected $_blockParams;
    protected $_block = array();
    protected $_fromSite = null;
    protected $_duplicateId = null;

    public function getFromSite()
    {
        return $this->_fromSite;
    }

    public function setFromSite($fromSite)
    {
        $this->_fromSite = $fromSite;
        return $this;
    }

    public function getDuplicateId()
    {
        return $this->_duplicateId;
    }

    public function setDuplicateId($duplicateId)
    {
        $this->_duplicateId = $duplicateId;
        return $this;
    }

    public function getBlock()
    {
        return $this->_block;
    }

    public function setBlock(array $block = null)
    {
        if ($block)
            $this->_block = $block;
        else
        {
            $oBlock = new BlocksObject();
            $this->_block = $oBlock->setSiteOrigin($this->_fromSite)
                ->populate($this->_blockID, Zend_Registry::get('languageID'));
        }

        $this->_blockParams = $this->_blockParams + $this->_block;

        return $this;
    }

    public function setDb($db)
    {
        $this->_db = $db;
        return $this;
    }

    public function setCurrentLang($lang)
    {
        $this->_current_lang = $lang;
    }

    public function __construct($blockID = null)
    {
        $this->_db = Zend_Registry::get('db');
        $this->_current_lang = Zend_Registry::get('languageID');

        if ($blockID)
        {
            $this->_blockID = $blockID;
            $_params = Cible_FunctionsBlocks::getBlockParameters($blockID);

            foreach ($_params as $param)
            {
                $this->_blockParams[$param['P_Number']] = $param['P_Value'];
            }
        }
        else
        {

            $this->_blockID = null;
            $this->_blockParams = array();
        }
    }

    public function getDetails($id)
    {
        $select = $this->_db->select();

        $select->from('NewsData', array('ND_ID', 'ND_Date', 'ND_ReleaseDate', 'ND_CategoryID'))
            ->distinct()
            ->join('NewsIndex', 'NewsIndex.NI_NewsDataID = NewsData.ND_ID')
            ->where('NewsIndex.NI_LanguageID = ?', $this->_current_lang)
            ->where('NewsData.ND_ID = ?', $id)
            ->where('NewsIndex.NI_Status = ?', 1);

        $news = $this->_db->fetchAll($select);

        return $news;
    }

    public function getList($limit = null, $filter = null)
    {
        $this->_checkCategoryParam();
        $select = $this->_db->select();

        $select->from('NewsData', array('ND_ID', 'ND_CategoryID', 'ND_Date', 'ND_ReleaseDate'))
            ->distinct()
            ->join('NewsIndex', 'NewsIndex.NI_NewsDataID = NewsData.ND_ID')
            ->where('NewsIndex.NI_LanguageID = ?', $this->_current_lang)
            ->where('NewsData.ND_CategoryID = ?', $this->_blockParams[1])
            ->where('NewsIndex.NI_Status = ?', 1)
            ->where('NewsData.ND_ReleaseDate <= ?', date('Y-m-d'))
            ->where('NewsData.ND_ReleaseDateEnd >= NewsData.ND_ReleaseDate OR NewsData.ND_ReleaseDateEnd = "0000-00-00" ')
            ->where('NewsData.ND_ReleaseDateEnd >= ? OR NewsData.ND_ReleaseDateEnd = "0000-00-00" ', date('Y-m-d'))
            ->order($this->_blockParams[4]);
        if ($this->_blockParams[4] === 'ND_Date DESC')
            $select->order('ND_ID DESC');
        if ($this->_blockParams[4] === 'ND_Date ASC')
            $select->order('ND_ID ASC');

        if ($filter)
            $select->where('ND_Date like ?', $filter .'%');
        if ($limit)
            $select->limit($limit);

        $news = $this->_db->fetchAll($select);

        return $news;
    }

    public function getOtherNews($limit = null, $not_ID)
    {
        $this->_checkCategoryParam();
        $select = $this->_db->select();

        $select->from('NewsData', array('ND_ID', 'ND_Date', 'ND_ReleaseDate'))
            ->distinct()
            ->join('NewsIndex', 'NewsIndex.NI_NewsDataID = NewsData.ND_ID')
            ->where('NewsIndex.NI_LanguageID = ?', $this->_current_lang)
            ->where('NewsData.ND_CategoryID = ?', $this->_blockParams[1])
            ->where('NewsIndex.NI_Status = ?', 1)
            ->where('NewsData.ND_ID <> ?', $not_ID)
            ->where('NewsData.ND_ReleaseDate <= ?', date('Y-m-d'))
            ->order($this->_blockParams[4]);

        if ($limit)
            $select->limit($limit);

        return $this->_db->fetchAll($select);
    }

    public function getBlockParam($param_name)
    {
        return $this->_blockParams[$param_name];
    }

    public function getBlockParams()
    {
        return $this->_blockParams;
    }

    /**
     * Fetch the id of a news according the formatted string from URL.
     *
     * @param string $string
     *
     * @return int Id of the searched news
     */
    public function getIdByName($string)
    {
        $select = $this->_db->select();
        $select->from('NewsIndex', 'NI_NewsDataID')
            ->where("NI_ValUrl = ?", $string);
        $id = $this->_db->fetchRow($select);
        return $id['NI_NewsDataID'];
    }

    /**
         * Fetch the valUrl of a news for the language switcher.
         *
         * @param int $id
         *
         * @return string $valUrl
         */
    public function getValUrl($id, $lang = 1){
        $select = $this->_db->select();
        $select->from('NewsIndex','NI_ValUrl')
                ->where("NI_NewsDataID = ?", $id)
                ->where("NI_LanguageID = ?", $lang);

        $valUrl = $this->_db->fetchOne($select);
        return $valUrl;
    }

    public function getFilterArchive()
    {
        $select = $this->_db->select()
            ->distinct()
            ->from('NewsData', array('dates' => 'ND_Date', 'nbNews' => "count(ND_ID)"))
            ->joinLeft('NewsIndex', 'NI_NewsDataID = ND_ID', array())
            ->where('NewsData.ND_CategoryID = ?', $this->_blockParams[1])
            ->where('NI_LanguageID = ?', $this->_current_lang)
            ->where('NI_Status = 1')
            ->group(array("Year(dates)", "Month(dates)"))
            ->order('ND_Date desc');

        $arraySelect = $this->_db->fetchAll($select);

        return $arraySelect;
    }

    private function _checkCategoryParam()
    {
        if (!empty($this->_fromSite))
        {
            $oParams = new ParametersObject();
            $params = $oParams->setSiteOrigin($this->_fromSite)
                ->setBlockId($this->_duplicateId)
                ->getData();

            if ($params['Param1'] != $this->_blockParams[1])
                $this->_blockParams[1] = $params['Param1'];
        }

    }

}
