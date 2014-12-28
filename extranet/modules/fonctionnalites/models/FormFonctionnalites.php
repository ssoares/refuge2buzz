<?php
class FormFonctionnalites extends Cible_Form_GenerateForm
{
    protected $_imageSrcL;
    protected $_imageSrcS;
    protected $_isNewImageL;
    protected $_isNewImageS;
    protected $_dataId;
    protected $_moduleName;
    protected $_filePath;
    protected $_object;
    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct($options)
    {
        $this->_addSubmitSaveClose = true;
        $this->_disabledLangSwitcher = false;

        if (!empty($options['object'])){
            $this->_object = $options['object'];
            unset($options['object']);
        }         
        
        parent::__construct($options);
    }
}