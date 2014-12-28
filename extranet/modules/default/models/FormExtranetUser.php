<?php

class FormExtranetUser extends Cible_Form
{

    public function __construct($options = null, $groupsData = array(), $isAdministrator)
    {
        // variable
        parent::__construct($options);
        $baseDir = $options['baseDir'];
        $exclude = array();
        $userId = 0;
        $profile = false;
        if (array_key_exists('userId', $options))
            $userId = $options['userId'];
        $exclude = array('field' =>'EU_ID', 'value'=> $userId);
        if (array_key_exists('profile', $options))
            $profile = $options['profile'];

        // lastname
        $lname = new Zend_Form_Element_Text('EU_LName');
        $lname->setLabel($this->getView()->getCibleText('form_label_lname'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->setAttrib('class', 'stdTextInput')
            ->setAttrib('escape', false);

        $this->addElement($lname);

        // firstname
        $fname = new Zend_Form_Element_Text('EU_FName');
        $fname->setLabel($this->getView()->getCibleText('form_label_fname'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->setAttrib('class', 'stdTextInput');

        $this->addElement($fname);

        // email
        $regexValidate = new Cible_Validate_Email();
        $regexValidate->setMessage($this->getView()->getCibleText('validation_message_emailAddressInvalid'), 'regexNotMatch');
        $emailNotFoundInDBValidator = new Zend_Validate_Db_NoRecordExists('Extranet_Users', 'EU_Email', $exclude);
        $emailNotFoundInDBValidator->setMessage($this->getView()->getClientText('validation_message_email_already_exists'), 'recordFound');

        $email = new Zend_Form_Element_Text('EU_Email');
        $email->setLabel($this->getView()->getCibleText('form_label_email'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addFilter('StringToLower')
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->addValidator($regexValidate)
            ->addValidator($emailNotFoundInDBValidator)
            ->setAttrib('class', 'stdTextInput');

        $this->addElement($email);

        // username
        $usernameNotFoundInDBValidator = new Zend_Validate_Db_NoRecordExists('Extranet_Users', 'EU_Username', $exclude);
        $usernameNotFoundInDBValidator->setMessage($this->getView()->getCibleText('validation_message_username_already_exists'), 'recordFound');

        $username = new Zend_Form_Element_Text('EU_Username');
        $username->setLabel($this->getView()->getCibleText('form_label_username'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('validation_message_empty_field'))))
            ->addValidator($usernameNotFoundInDBValidator)
            ->setAttrib('class', 'stdTextInput')
            ->setAttrib('autocomplete', 'off');

        $this->addElement($username);

        // new password
        $password = new Zend_Form_Element_Password('EU_Password');
        $password->setLabel($this->getView()->getCibleText('form_label_newPwd'))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('class', 'stdTextInput')
            ->setAttrib('autocomplete', 'off');
        ;

        $this->addElement($password);

        // password confirmation
        $passwordConfirmation = new Zend_Form_Element_Password('PasswordConfirmation');
        $passwordConfirmation->setLabel($this->getView()->getCibleText('form_label_confirmNewPwd'))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('class', 'stdTextInput');

        if (!empty($_POST['EU_Password']))
        {
            $passwordConfirmation->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $this->getView()->getCibleText('error_message_password_isEmpty'))));

            $Identical = new Zend_Validate_Identical($_POST['EU_Password']);
            $Identical->setMessages(array('notSame' => $this->getView()->getCibleText('error_message_password_notSame')));
            $passwordConfirmation->addValidator($Identical);
        }
        $this->addElement($passwordConfirmation);


        $show = new Zend_Form_Element_Checkbox('EU_ShowError');
        $show->setLabel($this->getView()->getCibleText('show_super_user_error'));
        $show->setDecorators(array(
            'ViewHelper',
            array('label', array('placement' => 'append')),
            array(array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'label_after_checkbox')),
        ));
        $this->addElement($show);


        if ($profile <> true)
        {
            $sitesList = new Zend_Form_Element_MultiCheckbox('EU_SiteAccess');
            $sitesList->setDecorators(array(
                'ViewHelper',
                array('description', array('placement' => 'prepend', 'tag' => 'span', 'class' => 'sitesList')),
                array(
                    array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'checkbox_list_sites')
                ),
            ));
            $sites = $this->getView()->siteList(array('getValues' => true));
            $sitesList->addMultiOptions($sites);
            $sitesList->setDescription($this->getView()->getCibleText('form_label_sitesList_description'));
            $sitesList->setSeparator('');
            $this->addElement($sitesList);

            $defaultSite = new Zend_Form_Element_Select('EU_DefaultSite');
            $defaultSite->setLabel($this->getView()->getCibleText('form_label_EU_DefaultSite'));
            $defaultSite->addMultiOption('0', $this->getView()->getCibleText('form_select_default_label'));

            $this->addElement($defaultSite);
            // html text
            $textAdministratorGroup = new Cible_Form_Element_Html('htmlAdministratorGroup', array('value' => $this->getView()->getCibleText('label_administrator_actives')));
            $this->addElement($textAdministratorGroup);

            $checkBox = new Zend_Form_Element_MultiCheckbox('groups');
            $checkBox->setDecorators(array(
                'ViewHelper',
                array(
                    array('row' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'checkbox_list')
                ),
            ));
            //$checkBox->setDescription('<em>Example:</em> mydomain.com')
            //->addDecorator('Description', array('escape' => false));
            //show administrator group (first level)
            if ($isAdministrator == 1)
            {
                $groupAdmin1 = Cible_FunctionsAdministrators::getAdministratorGroupData(1)->toArray();
                $checkBox->addMultiOption("1", $groupAdmin1['EGI_Name'] . " (" . $groupAdmin1['EGI_Description'] . ")");
            }

            $groupAdmin = Cible_FunctionsAdministrators::getAdministratorGroupData(2)->toArray();
            $checkBox->addMultiOption("2", $groupAdmin['EGI_Name'] . " (" . $groupAdmin['EGI_Description'] . ")");

            $i = 0;
            foreach ($groupsData as $group)
            {
                if ($group['EG_Status'] == 'active')
                {
                    $checkBox->addMultiOption($group['EG_ID'], $group['EGI_Name'] . " (" . $group['EGI_Description'] . ")");
                }

                $i++;
            }
            $this->addElement($checkBox);
        }
    }

}
