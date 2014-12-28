<?php
/**
 * Module Catalog
 * Management of the products.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: FormProducts.php 1295 2013-10-17 17:34:00Z ssoares $id
 */

/**
 * Form to add a new product.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: FormProducts.php 1295 2013-10-17 17:34:00Z ssoares $id
 */
class FormProducts extends Cible_Form_GenerateForm
{
    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct($options)
    {
        $this->_addSubmitSaveClose = true;
        if (!empty($options['object']))
        {
            $this->_object = $options['object'];
            unset($options['object']);
        }
        parent::__construct($options);
        $this->getDisplayGroup('productFormLeft')->setLegend(null);
        $this->getDisplayGroup('productFormLeftBis')->setLegend(null);
        $this->getDisplayGroup('productFormRight')->setLegend(null);
        $this->getDisplayGroup('productFormBottom')->setLegend(null);

        $moreImg = new Cible_Form_Element_Html('moreImg', array('value' => '<a href="#" id="moreImg">[Ajouter une image]</a>'));
        $moreImg->setOrder(21)
            ->setDecorators(array(
            'ViewHelper',
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'id' => 'additionalImg'))

        ));
        $this->addElement($moreImg);
        $html = new Cible_Form_Element_Html('associateProducts');
        $this->addElement($html);
        $this->addDisplayGroup(array('associateProducts'), 'bottom');
        $bottom = $this->getDisplayGroup('bottom');
        $bottom->setLegend(null)->setOrder(100)
            ->removeDecorator('HtmlTag');
        $bottom->removeDecorator('DtDdWrapper');
    }

    public function populate(array $values)
    {

        foreach ($values as $key => $value)
        {
            $tmpKeys = explode('_', $key);
            if (in_array('tmp', $tmpKeys))
            {
                $tmp = str_replace('_tmp', '_preview', $key);
                if (!empty($value))
                    $this->getElement($tmp)->setImage($value);
                else
                    $this->getElement($tmp)->setImage($this->getView()->baseUrl() . "/icons/image_non_ disponible.jpg");

                unset($values[$key]);
            }
            elseif (in_array($key, array('P_Warranty', 'P_ImgDim')))
            {
                if (empty ($value))
                    $this->getElement($key . '_preview')->setImage($this->getView()->baseUrl() . "/icons/image_non_ disponible.jpg");
                else
                {
                    $value = $this->_options['imgBasePath'] . $this->_options['nameSize'] . $value;
                    $this->getElement($key . '_preview')->setImage($value);
                }

            }

        }

        return parent::populate($values);
    }

    public function render(Zend_View_Interface $view = null)
    {
        if ($this->getView()->joinAssociation)
        {
            $html = $this->getElement('associateProducts');
            $html->setValue(Cible_FunctionsAssociationElements::getNewAssociationSetBox(
                    'products',
                    'P_',
                    'PI_Name',
                    '1',
                    $this->getView()->getCibleText('form_label_PI_Name_associate'),
                    $this->getView()->data,
                    $this->getView()->related))
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');
        }
        return parent::render($view);
    }
}
