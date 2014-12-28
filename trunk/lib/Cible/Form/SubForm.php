<?php

class Cible_Form_SubForm extends Zend_Form_SubForm
{
    protected $_addRequiredAsterisks = true;
    
    public function __construct($options = null)
    {
//        $this->_disabledDefaultActions = true;
        parent::__construct($options);
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->_addSubFormAsteriks($this);
        $render = parent::render($view);
        
        return $render;
    }

    protected function _addSubFormAsteriks($subForms, $_element = null)
    {
        
        foreach ($this->getElements() as $name => $_element)
        {

            if ($_element->getType() == 'Cible_Form_Element_Editor')
            {
                $this->getView()->headScript()->appendFile($this->getView()->baseUrl() . '/js/tinymce/tinymce.min.js');
                break;
            }

            if ($_element->isRequired() && $this->_addRequiredAsterisks)
            {
                $_element->setLabel("{$_element->getLabel()} <span class='field_required'>*</span>");
            }
        }
        
        if (is_array($this))
        {
            $tmpForm = current($this);
            }
        else{
            $tmpForm = $this->getSubForms();}
            
        if ($tmpForm instanceof Zend_Form_SubForm || $tmpForm instanceof Cible_Form_SubForm)
        {
            var_dump($tmpForm->getName());
            
            $this->_addSubFormAsteriks($tmpForm);
        }
    }
}
