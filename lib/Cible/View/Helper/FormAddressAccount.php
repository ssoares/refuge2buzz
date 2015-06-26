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
class Cible_View_Helper_FormAddressAccount extends Zend_View_Helper_FormElement
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
    public function formAddressAccount(Zend_Form $form = null, array $options = array())
    {
        if (isset($options['mode'])){
            $this->_mode = $options['mode'];
        }
        if (isset($options['requiredValidator'])){
            $this->_requiredValidator = $options['requiredValidator'];
        }
        $accountAddress = new Cible_Form_SubForm();
        $accountAddress->setName('address')
            ->setLegend(Cible_Translation::getCibleText('fieldset_address'))
            ->setAttrib('class', 'identificationClass subFormClass')
            ->removeDecorator('DtDdWrapper');
        $addr = new Cible_View_Helper_FormAddress($accountAddress);
        $addr->enableFields(array(
            'firstAddress',
            'firstTel',
            'secondTel',
            'cityTxt',
            'zipCode',
            'country',
            'state',
        ));

        $addr->formAddress();
        $accountAddress->getElement('AI_FirstAddress')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-12'))));
        $accountAddress->setRowDecorator(array($accountAddress->getElement('AI_FirstAddress')->getName()), 'addrOne');
        $accountAddress->getElement('AI_FirstTel')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $accountAddress->getElement('AI_SecondTel')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $secRow = array(
            $accountAddress->getElement('AI_FirstTel')->getName(),
            $accountAddress->getElement('AI_SecondTel')->getName(),
            );
        $accountAddress->setRowDecorator($secRow, 'addrTwo');
        $accountAddress->getElement('A_CityTextValue')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $accountAddress->getElement('A_ZipCode')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $thirdRow  = array(
            $accountAddress->getElement('A_CityTextValue')->getName(),
            $accountAddress->getElement('A_ZipCode')->getName(),
            );
        $accountAddress->setRowDecorator($thirdRow, 'addrThree');
        $accountAddress->getElement('A_StateId')->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $accountAddress->getElement('A_CountryId')
            ->setAttrib('class', 'full-width')
            ->setDecorators(array('ViewHelper', 'Errors',
                array('Label', array('class'=> 'full-width') ),
                array('row' => 'HtmlTag', array('tag' => 'div', 'class' => 'col-md-6'))));
        $fourthRow = array(
            $accountAddress->getElement('A_StateId')->getName(),
            $accountAddress->getElement('A_CountryId')->getName(),
            );
        $accountAddress->setRowDecorator($fourthRow, 'addrFour', array('class' => 'row '));
        $accountAddress->setDecorators(array('FormElements', 'Fieldset'))
            ->setAttrib('class', 'col-md-6 clear-left');
        $form->addSubForm($accountAddress, 'address');
        $config = Zend_Registry::get('config');
        $defaultStates = $config->address->default->states;
        $hiddenSrc = new Zend_Form_Element_Hidden('selectedState');
        $hiddenSrc->setValue($defaultStates);
        $hiddenSrc->setDecorators(array('ViewHelper'));
        $form->addElement($hiddenSrc);
    }

}