<?php


class FormBecomeClient extends Cible_Form
{

    protected $_mode = 'add';

    public function __construct($options = null)
    {
        $this->_disabledDefaultActions = true;
        parent::__construct($options);
        $baseDir = $this->getView()->baseUrl();

        if (!empty($options['mode']) && $options['mode'] == 'edit')
            $this->_mode = 'edit';
        else
            $this->_mode = 'add';

        $langId = Zend_Registry::get('languageID');
        $this->setAttrib('id', 'accountManagement');

        // Salutation
        $salutation = new Zend_Form_Element_Select('salutation');
        $salutation->setLabel($this->getView()->getCibleText('form_label_salutation'))
            ->setAttrib('class', 'smallSelect')
            ->setAttrib('tabindex', '1')
        ;

        $greetings = $this->getView()->getAllSalutation();
        foreach ($greetings as $greeting)
        {
            $salutation->addMultiOption($greeting['S_ID'], html_entity_decode($greeting['ST_Value'], null, 'UTF-8'));
        }

        // language hidden field
        $language = new Zend_Form_Element_Hidden('language', array('value' => $langId));
        $language->removeDecorator('label');

        // Language
//            $languages = new Zend_Form_Element_Select('language');
//            $languages->setLabel( $this->getView()->getCibleText('form_label_language') )
//                ->setAttrib('class', 'stdSelect')
//                ->setAttrib('tabindex','9')
//                ->setOrder(9);
//            foreach( Cible_FunctionsGeneral::getAllLanguage() as $lang ){
//                $languages->addMultiOption($lang['L_ID'], $lang['L_Title']);
//            }
        // FirstName
        $firstname = new Zend_Form_Element_Text('firstName');
        $firstname->setLabel($this->getView()->getCibleText('form_label_fName'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->setAttribs(array('class' => 'stdTextInput'))
            ->setAttrib('tabindex', '2')
        ;

        // LastName
        $lastname = new Zend_Form_Element_Text('lastName');
        $lastname->setLabel($this->getView()->getCibleText('form_label_lName'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->setAttribs(array('class' => 'stdTextInput'))
            ->setAttrib('tabindex', '3')
        ;

        // email
        $regexValidate = new Cible_Validate_Email();
        $regexValidate->setMessage($this->getView()->getCibleText('validation_message_emailAddressInvalid'), 'regexNotMatch');

        $emailNotFoundInDBValidator = new Zend_Validate_Db_NoRecordExists('GenericProfiles', 'GP_Email');
        $emailNotFoundInDBValidator->setMessage($this->getView()->getClientText('validation_message_email_already_exists'), 'recordFound');

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel($this->getView()->getCibleText('form_label_email'))
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addFilter('StringToLower')
            ->addValidator($regexValidate)
            ->setAttribs(array('maxlength' => 50, 'class' => 'stdTextInput'))
            ->setAttrib('tabindex', '6');

        if ($this->_mode == 'add')
            $email->addValidator($emailNotFoundInDBValidator);
        // email
        // password
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel($this->getView()->getCibleText('form_label_newPwd'));
        $validatePassword = new Cible_Validate_Password();
        $password->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('class', 'stdTextInput')
            ->setAttrib('tabindex', '7')
            ->addValidator($validatePassword)
            ->setRequired(true)
//                ->setOrder(6)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))));
        // password
        // password confirmation
        $passwordConfirmation = new Zend_Form_Element_Password('passwordConfirmation');
        $passwordConfirmation->setLabel($this->getView()->getCibleText('form_label_confirmPwd'));

        $passwordConfirmation->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
//                ->setOrder(7)
            ->setAttrib('class', 'stdTextInput')
            ->setAttrib('tabindex', '8')
            ->setDecorators(array(
                'ViewHelper',
                array(array('row' => 'HtmlTag'), array('tag' => 'dd')),
                array('label', array('class' => 'test', 'tag' => 'dt', 'tagClass' => 'alignVertical')),
            ));
        ;

        if (!empty($_POST['identification']['password']))
        {
            $passwordConfirmation->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('error_message_password_isEmpty'))));

            $Identical = new Zend_Validate_Identical($_POST['identification']['password']);
            $Identical->setMessages(array('notSame' => $this->getView()->getCibleText('error_message_password_notSame')));
            $passwordConfirmation->addValidator($Identical);
        }

        // Submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submitLabel = $this->getView()->getCibleText('form_account_button_submit');
        if ($this->_mode == 'edit')
            $submitLabel = $this->getView()->getCibleText('button_submit');

        $submit->setLabel($submitLabel)
            ->setAttrib('class', 'stdButton subscribeButton1-' . Zend_Registry::get("languageSuffix"));



        /*  Identification sub form */
        $identificationSub = new Zend_Form_SubForm();
        $identificationSub->setName('identification')
            ->removeDecorator('DtDdWrapper');
//        $identificationSub->setLegend($this->getView()->getCibleText('form_account_subform_identification_legend'));
        $identificationSub->setAttrib('class', 'identificationClass subFormClass');
        $identificationSub->addElement($language);
        $identificationSub->addElement($salutation);
        $identificationSub->addElement($firstname);
        $identificationSub->addElement($lastname);
        $identificationSub->addElement($email);
        $identificationSub->addElement($password);
        $identificationSub->addElement($passwordConfirmation);

        $this->addSubForm($identificationSub, 'identification');

        $this->addElement($submit);


        $submit->setDecorators(array(
            'ViewHelper',
            array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'account-submit')),
        ));

    }

    public function isValid($data)
    {
        $isValid = parent::isValid($data);

        return $isValid;
    }
}
