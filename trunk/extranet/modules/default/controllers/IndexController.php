<?php

/** Zend_Controller_Action */

class IndexController extends Cible_Extranet_Controller_Action
{
    function indexAction()
    {
      

    }

    public function dictionnaryAction(){
        $identifier = $this->_getParam('identifier');
        $lang = $this->_getParam('lang');
        $type = $this->_getParam('type');

        $this->view->assign('success', 'false');

        $dictionaryForm = new FormDictionnary();

        if ( $this->_request->isPost() ){
            $formData = $this->_request->getPost();
            if ($dictionaryForm->isValid($formData)) {
                Cible_Translation::set($identifier, $type, $formData['ST_Value'], $lang);
                $this->view->assign('success', 'true');
                $this->view->assign('value', $formData['ST_Value']);
            } else {
                $dictionaryForm->populate($formData);
            }
        } else {
            $data = array(
                'ST_Identifier' => $identifier,
                'ST_Value'=> Cible_Translation::__($identifier,$type, $lang),
                'ST_LangID' => $lang,
                'ST_Type' => $type
            );

            $dictionaryForm->populate($data);
        }

        $this->view->assign('form', $dictionaryForm);


    }
}
?>
