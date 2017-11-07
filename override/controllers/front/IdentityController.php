<?php

class IdentityController extends IdentityControllerCore
{




   public function postProcess()
    {   
       
        if (Tools::isSubmit('submitIdentity')) {
            $cpf = Tools::getValue('cpf');
            $numberDoc = preg_replace("/[^0-9]/", "", $cpf);
            $objModuloCpf = Module::getInstanceByName('paggi');
            try {
                $objModuloCpf->cpfValidation($numberDoc);   
                PaggiCustomer::setCPFCustomerPS($this->customer, $numberDoc);
            } catch (Exception $exc) {
                $this->errors[] = Tools::displayError($exc->getMessage());
            }
        }
        parent::postProcess();
    }
}
