<?php

class Cible_Form_SubForm extends Zend_Form_SubForm
{
    protected $_addRequiredAsterisks = true;
    protected $_resume = false;

    public function __construct($options = null)
    {
        if (!empty($options['resume'])){
            $this->_resume = $options['resume'];
            unset($options['resume']);
        }
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
            if ($this->_resume){
                $_element->setAttrib('disabled', 'disabled');
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

            $this->_addSubFormAsteriks($tmpForm);
        }
    }

    public function setRowDecorator($elements, $name, $options = null )
    {
        $class = "row";
        $legend = null;
        if(isset($options['legend'])){
            $legend = $options['legend'];
        }
        if(isset($options['class'])){
            $class = $options['class'];
        }
        $fieldset = 'Fieldset';
        if(isset($options['fieldset_class'])){
            $fielsetclass = $options['fieldset_class'];
            $fieldset = array('Fieldset', array('class' => $fielsetclass));
        }
        $this->addDisplayGroup(
            $elements, $name,
            array(
                'legend' => $legend,
                'decorators' => array('FormElements', $fieldset,
                    array('HtmlTag',array('tag'=>'div', 'class' => $class))
                    )
            )
        );

    }
}
