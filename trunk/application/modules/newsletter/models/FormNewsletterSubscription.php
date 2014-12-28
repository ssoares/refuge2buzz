<?php

class FormNewsletterSubscription extends Cible_Form {

    public function __construct($options = null) {
        $this->_disabledDefaultActions = true;
        $baseDir = $this->getView()->baseUrl();
        parent::__construct($options);

        $this->getView()->jQuery()->addJavascriptFile("{$this->getView()->baseUrl()}/js/jquery/jquery.maskedinput.min.js");
        $script = <<< EOS

            $('.phone_format').mask('(999) 999-9999? x99999');
            $('.postalCode_format').mask('a9a 9a9');
            $('.birthDate_format').mask('9999-99-99');
EOS;

        $this->getView()->jQuery()->addOnLoad($script);


        $this->setAttrib('class', 'zendFormNewsletter');

        $defaultType = new Zend_Form_Element_Hidden('NP_TypeID');
        $defaultType->removeDecorator('Label')->removeDecorator('DtDdWrapper');
        $config = Zend_Registry::get('config');
        $defaultType->setValue($config->newsletter->defaultTypeId);
        $this->addElement($defaultType);
        $subscrDate = new Zend_Form_Element_Hidden('NP_SubscriptionDate');
        $subscrDate->removeDecorator('Label')->removeDecorator('DtDdWrapper');
        $subscrDate->setValue(date('Y-m-d'));
        $this->addElement($subscrDate);

        // Salutation
        $salutation = new Zend_Form_Element_Select('salutation');
        $salutation->setLabel('Salutation')
                ->setAttrib('class', 'smallTextInput');
        $greetings = $this->getView()->getAllSalutation();
        foreach ($greetings as $greeting) {
            $salutation->addMultiOption($greeting['S_ID'], $greeting['ST_Value']);
        }
        /* $salutation->setDecorators(array(
          'ViewHelper',
          array('label', array('placement' => 'prepend')),
          array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'dd_form dd_sexe'))
          ));
          $salutation->setAttrib('class', 'newsletter_form_element select_salutations'); */
        $this->addElement($salutation);

        //FirstName
        $firstname = new Zend_Form_Element_Text('firstName');
        $firstname->setLabel($this->getView()->getCibleText('newsletter_fo_form_label_fName'))
                ->setRequired(true)
                ->setAttrib('placeholder', $this->getView()->getCibleText('newsletter_fo_form_placeholder_fName'))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
                ->setAttrib('class', 'newsletter-firstname');

        /* $firstname->setDecorators(array(
          'ViewHelper',
          array('label', array('placement' => 'prepend')),
          array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'dd_form dd_prenom'))
          ));
          $firstname->setAttrib('class', 'newsletter_form_element text_prenom'); */
        $this->addElement($firstname);

        // LastName
        $lastname = new Zend_Form_Element_Text('lastName');
        $lastname->setLabel($this->getView()->getCibleText('newsletter_fo_form_label_lName'))
                ->setRequired(true)
                ->addFilter('StripTags')
                ->setAttrib('placeholder', $this->getView()->getCibleText('newsletter_fo_form_placeholder_lName'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
                ->setAttrib('class', 'stdTextInput');
        /* $lastname->setDecorators(array(
          'ViewHelper',
          array('label', array('placement' => 'prepend')),
          array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'dd_form dd_nom'))
          )); */
        //$lastname->setAttrib('class', 'newsletter_form_element text_nom');
        $this->addElement($lastname);

        // email
        $regexValidate = new Cible_Validate_Email();
        $regexValidate->setMessage($this->getView()->getCibleText('validation_message_emailAddressInvalid'), 'regexNotMatch');

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel($this->getView()->getCibleText('newsletter_fo_form_label_email'))
                ->setRequired(true)
                ->setAttrib('placeholder', $this->getView()->getCibleText('newsletter_fo_form_placeholder_email'))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter('StringToLower')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
                ->addValidator($regexValidate)
                ->setAttrib('class', 'stdTextInput');
        if (isset($options['email']) && $options['email'] != '')
            $email->setValue($options['email']);
        /* $email->setDecorators(array(
          'ViewHelper',
          array('label', array('placement' => 'prepend')),
          array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'dd_form dd_email'))
          )); */
        // $email->setAttrib('class', 'newsletter_form_element text_email');
        $this->addElement($email);




        $termsAgreement = new Zend_Form_Element_Checkbox('termsAgreement');
        $langId = Zend_Registry::get('languageID');
        $title = $this->_config->site->title->$langId;
        $replace = array('##SITENAME##' => $title);
        $termsAgreement->setLabel($this->getView()->getCibleText('form_label_agree', null, $replace));
        $termsAgreement->setAttrib('class', 'long-text')->setUncheckedValue(null);
        $termsAgreement->setDecorators(array(
            'ViewHelper',
            array('label', array('placement' => 'append')),
            'Errors',
            array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'label_after_checkbox largedd')),
        ));
        $termsAgreement->setRequired(true);
        $termsAgreement->addValidator('notEmpty', true, array(
            'messages' => array(
                'isEmpty' => $this->getView()->getClientText('terms_agreement_error_message')
            )
        ));
        $this->addElement($termsAgreement);

        // Captcha
        $this->getView()->formAddCaptcha($this);

        // action button
        $subscribeButton = new Zend_Form_Element_Submit('subscribe');
        $subscribeButton->setLabel($this->getView()->getCibleText('button_submit'))
                ->setAttrib('id', 'submitSave')
                ->setAttrib('class', 'stdButton link-button')
                ->removeDecorator('Label')
                ->removeDecorator('DtDdWrapper');
        $this->addElement($subscribeButton);


        $requiredFields = new Zend_Form_Element_Hidden('RequiredFields');
        $requiredFields->setLabel('<span class="field_required">*</span>'
                . $this->getView()->getCibleText('form_field_required_label')
                . '<br /><br />');

        $requiredFields->setDecorators(array(
            'ViewHelper',
            array('label', array('placement' => 'append')),
            array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'label_required_fields'))
        ));
        $this->addElement($requiredFields);

        $this->addDisplayGroup(array('refresh_captcha', 'captcha', 'subscribe'), 'captcha-fieldset');


        $invi = new Zend_Form_Element_Hidden('language');
        $invi->setValue(Zend_Registry::get("languageID"));
        $this->addElement($invi);



        /*  $elements = $this->getElements();
          foreach($elements as $element) {
          $element->removeDecorator('DtDdWrapper')
          ->removeDecorator('HtmlTag');
          //->removeDecorator('Label');
          } */

        // reset form decorators to remove the 'dl' wrapper
        $this->formatDivDecorators();
    }

    public function formatDivDecorators() {
        parent::formatDivDecorators();

        $this->getElement('termsAgreement')
                ->addDecorators(array(
                    array('Label', array('class' => 'form-label-captcha')),
                    array('HtmlTag', array('class' => 'form-captcha-div form-div'))
                ))
        ;
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
        $this->getElement('subscribe')
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
