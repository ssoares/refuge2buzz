<?php

/**
 * Module Catalog
 * Management of the sub categories of the catalog.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: FormSubCategories.php 453 2011-04-14 04:16:53Z ssoares $id
 */

/**
 * Form to add a new sub category.
 *
 * @category  Extranet_Module
 * @package   Extranet_Module_Catalog
 * @copyright Copyright (c)2010 Cibles solutions d'affaires
 *            http://www.ciblesolutions.com
 * @license   Empty
 * @version   $Id: FormSubCategories.php 453 2011-04-14 04:16:53Z ssoares $id
 */
class FormSubCategories extends Cible_Form_Multilingual
{

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct($options)
    {
        parent::__construct($options);

        $imageSrc = $options['imageSrc'];
        $dataId = $options['dataId'];
        $isNewImage = $options['isNewImage'];
        $moduleName = $options['moduleName'];

        if ($dataId == '')
            $pathTmp = "../../../../../data/images/"
                    . $moduleName . "/tmp";
        else
            $pathTmp = "../../../../../data/images/"
                    . $moduleName . "/"
                    . $dataId . "/tmp";

        $config = Zend_Registry::get('config');

        // Name of the product line
        $name = new Zend_Form_Element_Text('SCI_Name');
        $name->setLabel(
                        $this->getView()->getCibleText('form_subcategory_name_label'))
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator(
                        'NotEmpty',
                        true,
                        array(
                            'messages' => array(
                                'isEmpty' => $this->getView()->getCibleText(
                                        'validation_message_empty_field')
                            )
                        )
                )
                ->setDecorators(
                        array(
                            'ViewHelper',
                            array('label', array('placement' => 'prepend')),
                            array(
                                'Errors',
                                array('placement' => 'append')
                            ),
                            array(
                                array('row' => 'HtmlTag'),
                                array(
                                    'tag' => 'dd',
                                    'class' => 'form_title_inline',
                                    'id' => 'title')
                            ),
                        )
                )
                ->setAttrib('class', 'stdTextInput ');

        $label = $name->getDecorator('Label');
        $label->setOption('class', $this->_labelCSS);

        $this->addElement($name);

        $oCategories = new CatalogCategoriesObject();
        $listCat = $oCategories->getAll(Zend_Registry::get('currentEditLanguage'));

        $categories = new Zend_Form_Element_Select('SC_CategoryID');
        $categories->setLabel(
                        $this->getView()->getCibleText('form_select_category_label'))
                ->setRequired(true)
                ->addValidator(
                        'NotEmpty',
                        true,
                        array(
                            'messages' => array(
                                'isEmpty' => $this->getView()->getCibleText(
                                        'validation_message_empty_field')
                            )
                        )
                )
                ->setDecorators(
                        array(
                            'ViewHelper',
                            array('label', array('placement' => 'prepend')),
                            array(
                                'Errors',
                                array('placement' => 'append')
                            ),
                            array(
                                array('row' => 'HtmlTag'),
                                array(
                                    'tag' => 'dd',
                                    'class' => 'form_title_inline',
                                    'id' => 'title')
                            ),
                        )
                )
                ->setAttrib('class', 'largeSelect');

        $categories->addMultiOption('', $this->getView()->getCibleText('form_select_default_label'));
        foreach ($listCat as $data)
        {
            $categories->addMultiOption($data['CC_ID'], $data['CCI_Name']);
        }

        $this->addElement($categories);

        // ImageSrc
        $imageBanner = new Zend_Form_Element_Select('SC_BannerGroupID');
        $imageBanner->setLabel($this->getView()->getCibleText('form_banner_image_group_extranet'))->setAttrib('class','stdSelect');
        $imageBanner->addMultiOption('', 'Sans image');
        $group = new GroupObject();
        $groupArray = $group->groupCollection();
        foreach ($groupArray as $group1){
            $imageBanner->addMultiOption($group1['BG_ID'],$group1['BG_Name']);
        }
        $this->addElement($imageBanner);
        

        // id of the associated meta data
        $metaTagId = new Zend_Form_Element_Hidden('SCI_MetaId');
        $metaTagId->removeDecorator('Label');
        $this->addElement($metaTagId);
    }

}
