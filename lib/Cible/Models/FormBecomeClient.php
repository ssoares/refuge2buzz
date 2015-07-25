<?php


class FormBecomeClient extends Cible_Form
{
    protected $_mode = 'add';
    private $_requiredValidator = null;
    protected $_resume = false;
    protected $_urlReturn = '';
    protected $_shipFee = '';

    public function __construct($options = null)
    {
        $this->_requiredValidator = new Zend_Validate_NotEmpty();
        $this->_requiredValidator->setMessage($this->getView()->getCibleText('validation_message_empty_field'), 'isEmpty');
        if (isset($options['object'])){
//            $this->_object = new $options['object'];
            unset($options['object']);
        }
        $this->_mode = 'add';
        if (!empty($options['mode'])){
            $this->_mode = $options['mode'];
        }
        unset($options['mode']);
        if (!empty($options['resume'])){
            $this->_resume = $options['resume'];
            $this->_shipFee = $options['shipFee'];
            $this->_urlReturn = ltrim($options['urlReturn'], '/');
        }
        unset($options['shipFee']);
        unset($options['resume']);
        $this->_disabledDefaultActions = true;

        parent::__construct($options);

        $this->setAttrib('id', 'accountManagement');
        $profile= new Cible_Form_SubForm(array('resume' => $this->_resume));
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
        if ((isset($options['from']) && $options['from'] != 'order')){
            $profile->addElement($password);
            $profile->addElement($passwordConfirmation);
            $profile->setRowDecorator(array($password->getName(), $passwordConfirmation->getName()), 'three');
        }
        $profile->setDecorators(array('FormElements', 'Fieldset'));
        $this->addSubForm($profile, 'identification');

        $this->getView()->FormAddressAccount($this, array(
            'requiredValidator' => $this->_requiredValidator,
            'mode' => $this->_mode,
            'resume' => $this->_resume
            ));
        $this->getView()->FormAddressShipping($this, array(
            'requiredValidator' => $this->_requiredValidator,
            'mode' => $this->_mode,
            'resume' => $this->_resume));
        if ($this->_mode == 'add') {
//            $this->getView()->formAddCaptcha($this);
        }
        // Submit button
        $submit = new Zend_Form_Element_Submit('submitAccount',
            array('decorators' => array('ViewHelper')));
        $submitLabel = $this->getView()->getCibleText('form_account_button_submit');
        if ($this->_mode == 'edit')
            $submitLabel = $this->getView()->getCibleText('button_submit');

        $submit->setLabel($submitLabel)
            ->setAttrib('class', 'link-button');
        $this->addElement($submit);
        if (isset($options['from']) && $options['from'] != 'order'){
            $this->addDisplayGroup(array('captcha', 'refresh_captcha',
                'CAT_CampaignId','submitAccount'), 'columnBottom');
            $this->getDisplayGroup('columnBottom')->setLegend(null)
                ->setOrder(50)
                ->setAttrib('class', 'col-xs-12 col-md-7')
                ->removeDecorator('DtDdWrapper');
        }else{
            $submit->setAttrib('class', 'hidden');
        }
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
            if ($this->getSubForm('identification')->getElement('GP_Password')
                && empty($data['identification']['GP_Password'])
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
        if ($this->_resume){
            $this->getSubForm('address')->getElement('A_CountryId')->helper = 'formText';
            $this->getSubForm('address')->getElement('A_StateId')->helper = 'formText';
            $this->getSubForm('addressShipping')->getElement('A_CountryId')->helper = 'formText';
            $this->getSubForm('addressShipping')->getElement('A_StateId')->helper = 'formText';

            $catalogPage = Cible_FunctionsCategories::getPagePerCategoryView(1, 'list', 14, null, true);
            $url = Zend_Registry::get('absolute_web_root') . $catalogPage;
            $config = Zend_Registry::get('config');
            $this->setAction($config->payment->url);
            $payPalCmd = new Zend_Form_Element_Hidden('cmd',
                array('value' => $config->payment->cmd, 'decorators' => array('viewHelper')));
            $payPalUpload = new Zend_Form_Element_Hidden('upload',
                array('value' => $config->payment->upload, 'decorators' => array('viewHelper')));
            $payPalBusiness = new Zend_Form_Element_Hidden('business',
                array('value' => $config->payment->business, 'decorators' => array('viewHelper')));
            $payPalCharset = new Zend_Form_Element_Hidden('charset',
                array('value' => $config->payment->charset, 'decorators' => array('viewHelper')));
            $payPalCurrency = new Zend_Form_Element_Hidden('currency_code',
                array('value' => $config->payment->currency_code, 'decorators' => array('viewHelper')));
            $this->addElements(array($payPalCmd, $payPalUpload, $payPalBusiness, $payPalCharset, $payPalCurrency));

            $payPalRows = Zend_Registry::get('payPalRows');
            if ($this->_shipFee){
                $payPalRows[] = array('Shipping fees', 1, $this->_shipFee);
            }
            foreach($payPalRows as $key => $data)
            {
                $payPalName = new Zend_Form_Element_Hidden('item_name_' . $key,
                    array('value' => $data[0], 'decorators' => array('viewHelper')));
                $payPalQty = new Zend_Form_Element_Hidden('quantity_' . $key,
                    array('value' => $data[1], 'decorators' => array('viewHelper')));
                $payPalAmount = new Zend_Form_Element_Hidden('amount_' . $key,
                    array('value' => ($data[2]/$data[1]), 'decorators' => array('viewHelper')));
                $this->addElements(array($payPalName, $payPalQty, $payPalAmount));
            }

            $elemsToAdd = array();
            $elemsToAdd[] = new Zend_Form_Element_Hidden('return',
                array('value' => $this->_urlReturn, 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('shopping_url',
                array('value' => $url, 'decorators' => array('viewHelper')));

            $subId = $this->getSubForm('identification');
            $subAddr = $this->getSubForm('address');
            $lgId = $subId->getElement('GP_Language')->getValue();
            $lang = Cible_FunctionsGeneral::getLocalForLanguage($lgId);
            $elemsToAdd[] = new Zend_Form_Element_Hidden('lc',
                array('value' => $lang, 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('first_name',
                array('value' => $subId->getElement('GP_FirstName')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('last_name',
                array('value' => $subId->getElement('GP_LastName')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('address1',
                array('value' => $subAddr->getElement('AI_FirstAddress')->getValue(), 'decorators' => array('viewHelper')));
//            $elemsToAdd[] = new Zend_Form_Element_Hidden('address2',
//                array('value' => $subAddr->getElement('AI_SecondAddress')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('city',
                array('value' => $subAddr->getElement('A_CityTextValue')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('state',
                array('value' => $subAddr->getElement('A_StateId')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('zip',
                array('value' => $subAddr->getElement('A_ZipCode')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('country',
                array('value' => $subAddr->getElement('A_CountryId')->getValue(), 'decorators' => array('viewHelper')));
            $phone = explode(' ', $subAddr->getElement('AI_FirstTel')->getValue());
            $elemsToAdd[] = new Zend_Form_Element_Hidden('night_phone_a',
                array('value' => $phone[0], 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('night_phone_b',
                array('value' => $phone[1], 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('night_phone_c',
                array('value' => $phone[2], 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('login_email',
                array('value' => $subId->getElement('GP_Email')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('email',
                array('value' => $subId->getElement('GP_Email')->getValue(), 'decorators' => array('viewHelper')));
            $elemsToAdd[] = new Zend_Form_Element_Hidden('at',
                array('value' => $config->payment->token, 'decorators' => array('viewHelper')));


            $this->addElements($elemsToAdd);
        }
        return parent::render($view);
    }

}
