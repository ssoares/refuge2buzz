<?php

class Forms_IndexController extends Cible_Controller_Action {

    protected $_moduleID = 11;

    public function init() {
//        $this->_isSecured = false;
        parent::init();
    }

    public function formscontactAction() {
        $blockParamEmail = Cible_FunctionsBlocks::getBlockParameter($this->_getParam('BlockID'), '1');

        if (isset($blockParamEmail))
            $mailTo = $blockParamEmail;        

        $form = new FormContact(array('action' => $this->_request->getRequestUri(), 'disabledFieldsStatus' => true));
        
         
        
        $this->view->assign('form', $form);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();

            if (array_key_exists('submit', $formData)) {
                if ($form->isValid($formData)) {
                    // send the mail
                    $data = array(                        
                        'lastName' => $formData['name'],
                        'firstName' => "",
                        'email' => $formData['email'],
                        'comments' => $formData['commentaire'],
                        'phone' => $formData['phone'],
                        'language' => Zend_Registry::get('languageID'),
                    );
                    $options = array(
                        'send' => true,
                        'isHtml' => true,
                        'moduleId' => $this->_moduleID,
                        'event' => 'contact',
                        'type' => 'email',
                        'recipient' => 'admin',
                        'data' => $data
                    );                  
                    
                    if (!empty($mailTo))
                        $options['to'] = $mailTo;

                    $oNotification = new Cible_Notifications_Email($options);

                    $this->view->assign('inscriptionValidate', true);
                }
            }
        }
        else {
            
        }
    }

    public function captchaReloadAction() {
        $baseDir = $this->view->baseUrl() . '/';
        $captcha_image = new Zend_Captcha_Image(array(
            'captcha' => 'Word',
            'wordLen' => 6,
            'height' => 50,
            'width' => 150,
            'timeout' => 600,
            'dotNoiseLevel' => 0,
            'lineNoiseLevel' => 0,
            'font' => "/captcha/fonts/ARIAL.TTF",
            'imgDir' => "captcha/tmp",
            'imgUrl' => $baseDir . "captcha/tmp"
        ));

        $image = $captcha_image->generate();
        $captcha['id'] = $captcha_image->getId();
        $captcha['word'] = $captcha_image->getWord();
        $captcha['url'] = $captcha_image->getImgUrl() . $image . $captcha_image->getSuffix();

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        echo Zend_Json::encode($captcha);
    }

    public function thankYouAction() {
        
    }
    
    public function formsnewsletterfooterAction() {
        $mailTo = "jpbernard@gmail.com";
        $subscribeUrl = $this->view->baseUrl() . "/" . Cible_FunctionsCategories::getPagePerCategoryView(2, 'subscribe', 8, $this->view->languageId, false);
        
        //$form = new FormNewsletterFooter(array('action' => $this->_request->getRequestUri(), 'disabledFieldsStatus' => true));
        $form = new FormNewsletterFooter(array('action' => $subscribeUrl, 'disabledFieldsStatus' => true));
        $this->view->assign('form', $form);
        
        /*
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();

            if (array_key_exists('newslettersubmit', $formData)) {
                if ($form->isValid($formData)) {
                    // send the mail
                    $data = array(
                        'name' => '',//$formData['name'],
                        'email' => $formData['newsletteremail'],
                        'language' => Zend_Registry::get('languageID'),
                    );
                    $options = array(
                        'send' => true,
                        'isHtml' => true,
                        'moduleId' => $this->_moduleID,
                        'event' => 'newslettersignup',
                        'type' => 'email',
                        'recipient' => 'admin',
                        'data' => $data
                    );
                    if (!empty($mailTo))
                        $options['to'] = $mailTo;

                    $oNotification = new Cible_Notifications_Email($options);

                    $this->view->assign('inscriptionValidate', true);
                }
            }
        }*/
    }

}
