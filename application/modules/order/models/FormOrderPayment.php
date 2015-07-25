<?php
class FormOrderPayment extends Cible_Form
{

    public function __construct($options = null)
    {
        $this->_disabledDefaultActions = true;
        $readOnly = $options['readOnlyForm'];
//            $payement = $options['payMean'];
        $config   = Zend_Registry::get('config');
        unset($options['readOnlyForm']);
        unset($options['payMean']);

        parent::__construct($options);

        $this->setAttrib('id', 'accountManagement');

        $buttonLabel = $this->getView()->getClientText('form_label_confirm_order_btn');

//            if (in_array($payement, array('visa', 'mastercard')))
//            {
//                $this->setAction($config->payment->url);
//                $buttonLabel = $this->getView()->getClientText('form_label_confirm_payment_btn');
//            }

        // Account data summary
        $summary = new Cible_Form_Element_Html('summary',
                    array(
                        'value' => $readOnly
                    )
        );
        $summary->setDecorators(
                array(
                    'ViewHelper',
                    array('label', array('placement' => 'prepend')),
                    array(
                        array('row' => 'HtmlTag'),
                        array(
                            'tag' => 'dd',
                            'class' => 'form_title_inline left')
                    ),
                )
        );
        $this->addElement($summary);

        $business = new Zend_Form_Element_Hidden('business', array('value' => $config->payment->business));
        $this->addElement($business);
        $business->setDecorators(array('ViewHelper'));
        $cmd = new Zend_Form_Element_Hidden('cmd', array('value' => $config->payment->cmd));
        $cmd->setDecorators(array('ViewHelper'));
        $this->addElement($cmd);
        $upload = new Zend_Form_Element_Hidden('upload', array('value' => $config->payment->upload));
        $upload->setDecorators(array('ViewHelper'));
        $this->addElement($upload);
        $charset = new Zend_Form_Element_Hidden('charset', array('value' => $config->payment->charset));
        $charset->setDecorators(array('ViewHelper'));
        $this->addElement($charset);
        $currency = new Zend_Form_Element_Hidden('currency_code', array('value' => $config->payment->currency_code));
        $currency->setDecorators(array('ViewHelper'));
        $this->addElement($currency);
        $return = new Zend_Form_Element_Hidden('return', array('value' => $options['urlReturn']));
        $return->setDecorators(array('ViewHelper'));
        $this->addElement($return);
        $shopingUrl = new Zend_Form_Element_Hidden('shopping_url', array('value' => $options['urlReturn']));
        $shopingUrl->setDecorators(array('ViewHelper'));
        $this->addElement($shopingUrl);


        $this->setAction($config->payment->url);

    // Submit button
        $submit = new Zend_Form_Element_Submit('submitPayment');
        $submit->setLabel($buttonLabel)
            ->setAttrib('class','hidden')
            ->setDecorators(array('ViewHelper'));

        $this->addElement($submit);
    }
}
