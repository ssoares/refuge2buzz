<?php


class FormBecomeClient extends Cible_Form
{
    protected $_mode = 'add';
    private $_requiredValidator = null;

    public function __construct($options = null)
    {
        $this->_requiredValidator = new Zend_Validate_NotEmpty();
        $this->_requiredValidator->setMessage($this->getView()->getCibleText('validation_message_empty_field'), 'isEmpty');
        if (isset($options['object'])){
//            $this->_object = new $options['object'];
            unset($options['object']);
        }
        $this->_mode = 'add';
        if (!empty($options['mode']) && $options['mode'] == 'edit'){
            $this->_mode = 'edit';
        }
        unset($options['mode']);
        $this->_disabledDefaultActions = true;

        parent::__construct($options);

        $this->setAttrib('id', 'accountManagement');
        $profile= new Cible_Form_SubForm();
        $profile->setName('identification')
            ->setLegend(Cible_Translation::getCibleText('fieldset_profile'))
            ->setAttrib('class', 'identificationClass subFormClass col-xs-12 col-md-6')
            ->removeDecorator('DtDdWrapper');
        $langId = new Zend_Form_Element_Hidden('GP_Language',
            array('value' => Zend_Registry::get('languageID')));
        $langId->setDecorators(array('ViewHelper'));
        $profile->addElement($langId);
        $memberId = new Zend_Form_Element_Hidden('GP_MemberID');
        $memberId->setDecorators(array('ViewHelper'));
        if($this->_mode == 'edit'){
            $profile->addElement($memberId);
        }
        $idAddr = new Zend_Form_Element_Hidden('MP_AddressId');
        $idAddr->setDecorators(array('ViewHelper'));
        $this->addElement($idAddr);
        $emailValid = new Cible_Validate_Email();
        $emailValid->setMessage($this->getView()->getCibleText('validation_message_emailAddressInvalid'), 'regexNotMatch');
        $emailNotFoundInDBValidator = new Zend_Validate_Db_NoRecordExists('GenericProfiles', 'GP_Email');
        $emailNotFoundInDBValidator->setMessage($this->getView()->getClientText('validation_message_email_already_exists'), 'recordFound');
        $emailMain = new Zend_Form_Element_Text('GP_Email', array(
            'label' => $this->getView()->getCibleText('form_label_email'),
            'required' => 'true',
            'class' => 'col-md-12',
            ));
        $emailMain->addValidators(array($emailValid, $this->_requiredValidator))
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-12'))));
        $firstNameMain = new Zend_Form_Element_Text('GP_FirstName', array(
            'label' => $this->getView()->getCibleText('form_label_fname'),
            'class' => 'col-md-12',
            'required' => 'true',
            'decorators' => array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))),
            ));
        $firstNameMain->addValidator($this->_requiredValidator);
        $lastNameMain = new Zend_Form_Element_Text('GP_LastName', array(
            'label' => $this->getView()->getCibleText('form_label_lname'),
            'class' => 'col-md-12',
            'required' => 'true',
            'decorators' => array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))),
            ));
        $lastNameMain->addValidator($this->_requiredValidator);
        $salutationMain = new Zend_Form_Element_Select('GP_Salutation', array(
            'label' => Cible_Translation::getCibleText('form_label_salutation'),
            'class' => 'full-width',
            'required' => 'true',
            'decorators' => array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))),
            ));

        $greetings = $profile->getView()->getAllSalutation();
        foreach ($greetings as $greeting) {
            $salutationMain->addMultiOption($greeting['S_ID'], $greeting['ST_Value']);
        }
        $salutationMain->addValidator($this->_requiredValidator);

        // password
        $password = new Zend_Form_Element_Password('GP_Password', array(
            'label' => Cible_Translation::getCibleText('form_label_password'),
            'class' => 'full-width',
            'decorators' => array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))),
            ));
        $password->setValue("")
            ->addFilter('StripTags')->addFilter('StringTrim');

        // password confirmation
        $passwordConfirmation = new Zend_Form_Element_Password('passwordConfirmation', array(
            'label' => Cible_Translation::getCibleText('form_label_confirmPwd'),
            'class' => 'full-width',
            'decorators' => array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))),
            ));

        $passwordConfirmation->addFilter('StripTags')->addFilter('StringTrim');
        if ($this->_mode == 'add'){
            $password->setRequired()->addValidator($this->_requiredValidator);
            $passwordConfirmation->setRequired()->addValidator($this->_requiredValidator);
            $emailMain->addValidators(array($emailNotFoundInDBValidator));
        }

        $profile->addElement($salutationMain);
        $profile->setRowDecorator(array($salutationMain->getName()), 'one');
        $profile->addElement($firstNameMain);
        $profile->addElement($lastNameMain);
        $profile->setRowDecorator(array($firstNameMain->getName(),
            $lastNameMain->getName()), 'one-2');
        $profile->addElement($emailMain);
        $profile->setRowDecorator(array($emailMain->getName()), 'two');
        $profile->addElement($password);
        $profile->addElement($passwordConfirmation);
        $profile->setRowDecorator(array($password->getName(), $passwordConfirmation->getName()), 'three');
        $profile->setDecorators(array('FormElements', 'Fieldset'));
        $this->addSubForm($profile, 'identification');

        $this->getView()->FormAddressAccount($this, array(
            'requiredValidator' => $this->_requiredValidator,
            'mode' => $this->_mode));
        $this->getView()->FormAddressShipping($this, array(
            'requiredValidator' => $this->_requiredValidator,
            'mode' => $this->_mode));
        if ($this->_mode == 'add') {
//            $this->getView()->formAddCaptcha($this);
        }
        // Submit button
        $submit = new Zend_Form_Element_Submit('submitAccount');
        $submitLabel = $this->getView()->getCibleText('form_account_button_submit');
        if ($this->_mode == 'edit')
            $submitLabel = $this->getView()->getCibleText('button_submit');

        $submit->setLabel($submitLabel)
            ->setAttrib('class', 'link-button');

        $this->addElement($submit);
        $this->addDisplayGroup(array('captcha', 'refresh_captcha',
            'CAT_CampaignId','submitAccount'), 'columnBottom');
        $this->getDisplayGroup('columnBottom')->setLegend(null)
            ->setOrder(50)
            ->setAttrib('class', 'col-xs-12 col-md-7')
            ->removeDecorator('DtDdWrapper');
    }

    public function isValid($data)
    {
        if ((bool)$data['addressShipping']['duplicate']){
            $addrS = $this->getSubForm('addressShipping');
            foreach($addrS->getElements() as $key => $elem){
                $elem->clearValidators()->setRequired(false);
            }
        }
        if ($this->_mode == 'edit'){
            if (empty($data['identification']['GP_Password'])
                && empty($data['identification']['passwordConfirmation'])) {
                $this->getSubForm('identification')->getElement('GP_Password')
                    ->clearValidators()->setRequired(false);
                $this->getSubForm('identification')->getElement('passwordConfirmation')
                    ->clearValidators()->setRequired(false);
            }
        }
        $isValid = parent::isValid($data);

        return $isValid;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->setDecorators(array('FormElements', 'Form'));
        return parent::render($view);
    }

}
