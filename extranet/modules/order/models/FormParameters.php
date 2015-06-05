<?php

class FormParameters extends Cible_Form_GenerateForm
{
    public function __construct($options = null)
    {
        $this->_addSubmitSaveClose = true;
        if (!empty($options['object']))
        {
            $this->_object = $options['object'];
            unset($options['object']);
        }
        parent::__construct($options);
    }
}