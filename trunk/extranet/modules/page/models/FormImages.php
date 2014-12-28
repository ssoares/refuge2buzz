<?php
    
    class FormImages extends Cible_Form
    {
        public function __construct($options = null)
        {
            $this->_addSubmitSaveClose = false;
            parent::__construct($options);

            $imageSrcTmp   = $options['imageSrc']; 
            
            $isNewImage = $options['isNewImage'];     
            $pathTmp = $pathTmp = $this->_imagesFolder . $options['pathTmp'];;
            
            $imageSrc = "";
            if($imageSrcTmp!=""){
                $imageSrc = $options['pathZend'] . $imageSrcTmp;
            }

            $imageTmp  = new Zend_Form_Element_Hidden('ImageSrc_tmp');
            $imageTmp->removeDecorator('Label');
            $this->addElement($imageTmp);

            $imageTmpO  = new Zend_Form_Element_Hidden('ImageSrcOriginal_tmp');
            $imageTmpO->removeDecorator('Label');
            $imageTmpO->setValue($imageSrcTmp);
            $this->addElement($imageTmpO);

            
            $imageOrg  = new Zend_Form_Element_Hidden('ImageSrc_original');
            $imageOrg->removeDecorator('Label');
            $this->addElement($imageOrg);
            
            $imageOrg  = new Zend_Form_Element_Hidden('ImageSrc_original');
            $imageOrg->removeDecorator('Label');
            $imageOrg->setValue($imageSrcTmp);
            $this->addElement($imageOrg);

            $imageView = new Zend_Form_Element_Image('ImageSrc_preview', array('onclick'=>'return false;'));
            $imageView->setImage($imageSrc)
                ->setAttrib('class','ImageSrc_previewEntete');            
            $this->addElement($imageView);

            $imagePicker = new Cible_Form_Element_ImagePicker('ImageSrc', array( 'onchange' => "document.getElementById('imageView').src = document.getElementById('ImageSrc').value",
                                                                                    'associatedElement' => 'ImageSrc_preview',
                                                                                    'pathTmp'=>$pathTmp,
                                                                                    'contentID'=>''));
            $imagePicker->removeDecorator('Label');
            $this->addElement($imagePicker);            
         
        }
    }
?>