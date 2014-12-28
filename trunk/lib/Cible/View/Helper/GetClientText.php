<?php
    class Cible_View_Helper_GetClientText extends Zend_View_Helper_Abstract
    {
        public function getClientText($key, $lang = null, $replace = array()){
            return Cible_Translation::__($key, Cible_Translation::TRANSLATION_TYPE_CLIENT, $lang, $replace);
        }
    }