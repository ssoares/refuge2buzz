<?php
    class Cible_View_Helper_BaseUrl
    {
        public function baseUrl(){
            $frontController = Zend_Controller_Front::getInstance();
            return $frontController->getBaseUrl();
        }
    }