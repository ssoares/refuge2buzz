<?php

/* Pas un vrai script, j'ai mis ce code là pour m'aider à faire des formulaires */

class FormContact extends Cible_Form {

    public function __construct($options = null) {
        $baseUrl = Zend_Registry::get('web_root');
        $this->_disabledDefaultActions = true;
        parent::__construct($options);
        $baseDir = $this->getView()->baseUrl() . '/';
        $this->getView()->jQuery()->addJavascriptFile("{$this->getView()->baseUrl()}/js/jquery/jquery.maskedinput.min.js");


        $script1 = <<< EOS

            $('.phone_format').mask('(999) 999-9999? x99999');
            $('.postalCode_format').mask('a9a 9a9');
            $('.birthDate_format').mask('9999-99-99');

EOS;
        $this->getView()->headScript()->appendScript($script1);
        $script2 = <<< EOS

            function refreshCaptcha(id){
                $.getJSON('{$this->getView()->baseUrl()}/forms/index/captcha-reload',
                    function(data){
                        $("dd#dd_captcha img").attr({src : data['url']});
                        $("#"+id).attr({value: data['id']});
                });
            }

EOS;

        $this->getView()->headScript()->appendScript($script2);
        // last name
        $surname = new Zend_Form_Element_Text('surname');
        $surname
                //->setLabel($this->getView()->getCibleText('forms_label_name'))
                ->setAttrib('placeholder', $this->getView()->getCibleText('forms_label_placeholder_surname'))
        ->setAttrib('class', 'forms-input-lastname forms-input-text required-field')
        ;

        // first name
        $name = new Zend_Form_Element_Text('name');
        $name
                //->setLabel($this->getView()->getCibleText('forms_label_name'))
                ->setAttrib('placeholder', $this->getView()->getCibleText('forms_label_placeholder_name'))
        ->setAttrib('class', 'forms-input-firstname forms-input-text')
        ;
        //email
        $email = new Zend_Form_Element_Text('email');
        $email
                //->setLabel($this->getView()->getCibleText('form_label_email'))
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('error_field_required'))))
                ->addValidator('EmailAddress', true, array('messages' => Cible_Translation::getCibleText('validation_message_emailAddressInvalid')))
                ->setRequired(true)
                ->setAttrib('placeholder', $this->getView()->getCibleText('forms_label_placeholder_email'))
        ->setAttrib('class', 'forms-input-email forms-input-text required-field')
        ;

        // Commentaires
        $commentaire = new Zend_Form_Element_Textarea('commentaire');
        $commentaire
                //->setLabel($this->getView()->getCibleText('form_label_comments'))
                ->setRequired(false)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('rows', 10)
                ->setAttrib('placeholder', $this->getView()->getCibleText('forms_label_placeholder_comments'))
        ->setAttrib('class', 'stdTextarea required-field')
        ;

        $this->addElement($surname);
        $this->addElement($name);
        $this->addElement($email);
        $this->addElement($commentaire);

        // Captcha
        $this->getView()->formAddCaptcha($this);
        // Submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel($this->getView()->getCibleText('button_submit'))
                ->setAttrib('class', 'link-button')
        //->setAttrib('class', 'grayish-button')
        ;

        $this->addElement($submit);       
        
        $this->addDisplayGroup(array('refresh_captcha','captcha', 'submit'), 'captcha-fieldset');

        //$mobile = Zend_Registry::get('isMobile');
        $this->formatDivDecorators();
    }

    public function formatDivDecorators() {
        parent::formatDivDecorators();

        $this->getElement('captcha')
                ->removeDecorator('ViewHelper')
                ->addDecorators(array(
                    array('Label', array('class' => 'form-label-captcha')),
                    array('HtmlTag', array('class' => 'form-captcha-div form-div'))
                ))
        ;

        $this->getElement('refresh_captcha')
                ->addDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array('HtmlTag', array('class' => 'form-div form-div-refresh'))
                ))
        ;
        $this->getElement('submit')
                ->removeDecorator('DtDdWrapper')
                ->addDecorators(array(
                    array('HtmlTag', array('class' => 'form-div form-div-submit'))
                ))
        ;
        $captchaFieldset = $this->getDisplayGroup('captcha-fieldset');
        $captchaFieldset->setDecorators(array(
            'FormElements',
            array(
                array('fieldset' => 'HtmlTag'),
                array('tag' => 'fieldset', 'class' => 'captcha-fieldset')
            )))
                ;
    }

}

?>
