<?php

/**
 * Module Imageslibrary
 * Management of the featured elements.
 *
 * @category  Application_Module
 * @package   Application_Module_Imageslibrary
 *

 * @license   Empty
 * @version   $Id: FormImageslibrary.php 360 2013-02-05 22:38:38Z ssoares $
 */

/**
 * Form to manage images.
 *
 * @category  Application_Module
 * @package   Application_Module_Catalog
 *

 * @license   Empty
 * @version   $Id: FormImageslibrary.php 360 2013-02-05 22:38:38Z ssoares $id
 */
class FormImageslibrary extends Cible_Form
{

    public function __construct($options = null)
    {
        $this->_disabledDefaultActions = true;

        parent::__construct($options);        

        $filterCheckbox = new Zend_Form_Element_MultiCheckbox('keywords');
        $filterCheckbox->setLabel(
                $this->getView()->getClientText('form_label_specs'))
            ->setAttrib('class', 'largeSelect')
            ->setSeparator(' ');

        $filterCheckbox->addMultiOptions($options['keywords']);
        $this->addElement(($filterCheckbox));


        
        $this->addDisplayGroup(array('keywords'), 'groupRight');

//        $this->getDisplayGroup('groupLeft')->removeDecorator('DtDdWrapper');
        $this->getDisplayGroup('groupRight')->removeDecorator('DtDdWrapper');
    }

}
