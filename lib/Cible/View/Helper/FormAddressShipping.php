<?php
/**
 * Edith: Cible Framework
 *
 * @category   Cible
 * @package    Cible_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2010 Cible solutions (http://www.ciblesolutions.com)
 * @license
 */


/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a subform containing fields for the recurvise donation.
 *
 * @category   Cible
 * @package    Cible_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2010 Cible solutions (http://www.ciblesolutions.com)
 * @license
 */
class Cible_View_Helper_FormAddressShipping extends Zend_View_Helper_FormElement
{
    protected $_options = array();
    protected $_mode = array();
    protected $_requiredValidator = null;

    /**
     * Class cosntructor. Set the form if defined and other properties.
     *
     * @param Zend_Form $form    The form which we will add address fields.
     * @param array     $options An array of properties to set.
     *
     * @return void
     */
    public function formAddressShipping(Zend_Form $form = null, array $options = array())
    {
        if (isset($options['mode'])){
            $this->_mode = $options['mode'];
        }
        if (isset($options['requiredValidator'])){
            $this->_requiredValidator = $options['requiredValidator'];
        }
        $address = new Cible_Form_SubForm();
        $address->setName('addressShipping')
            ->setLegend(Cible_Translation::getCibleText('fieldset_addressShipping'))
            ->setAttrib('class', 'identificationClass subFormClass')
            ->removeDecorator('DtDdWrapper');
        $addr = new Cible_View_Helper_FormAddress($address);
        $addr->duplicateAddress($address);
        $addr->enableFields(array(
            'firstTel',
            'secondTel',
            'firstAddress',
            'cityTxt',
            'zipCode',
            'country',
            'state',
        ));

        $addr->formAddress();
        $address->getElement('AI_FirstAddress')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-12'))));
        $address->setRowDecorator(array($address->getElement('AI_FirstAddress')->getName()), 'addrOne');
        $address->getElement('AI_FirstTel')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $address->getElement('AI_SecondTel')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $secRow = array(
            $address->getElement('AI_FirstTel')->getName(),
            $address->getElement('AI_SecondTel')->getName(),
            );
        $address->setRowDecorator($secRow, 'addrTwo');
        $address->getElement('A_CityTextValue')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $address->getElement('A_StateId')->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $thirdRow  = array(
            $address->getElement('A_CityTextValue')->getName(),
            $address->getElement('A_ZipCode')->getName(),
            );
        $address->setRowDecorator($thirdRow, 'addrThree');
        $address->getElement('A_ZipCode')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $address->getElement('A_CountryId')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $fourthRow = array(
            $address->getElement('A_StateId')->getName(),
            $address->getElement('A_CountryId')->getName(),
            );
        $address->setRowDecorator($fourthRow, 'addrFour', array('class' => 'row '));
        $address->setAttrib('class', 'col-md-6');
        $hiddenId = new Zend_Form_Element_Hidden('MP_ShippingAddrId');
        $hiddenId->setDecorators(array('ViewHelper'));
        $address->addElement($hiddenId);
        $form->addSubForm($address, 'addressShipping');
    }

}