<?php

class AuthController extends AuthControllerCore
{
    protected function processSubmitAccount()
    {
        $cpf = Tools::getValue('cpf');
        
        $numberDoc = preg_replace("/[^0-9]/", "", $cpf);
        
        $objModuloCpf = Module::getInstanceByName('paggi');
        
        try {
           
            $objModuloCpf->cpfValidation($numberDoc); 
            
        } catch (Exception $exc) {
            $this->errors[] = Tools::displayError($exc->getMessage());
        }
        
        parent::processSubmitAccount();
    }
}
