<?php

class CustomerForm extends CustomerFormCore
{
    
    public function fillFromCustomer( Customer $customer )
    {
       

        $params = get_object_vars($customer);
        $params['id_customer'] = $customer->id;
        $params['birthday'] = $customer->birthday === '0000-00-00' ? null : Tools::displayDate($customer->birthday);

        $objModuloCpf = Module::getInstanceByName('paggi');

        $params['cpf'] = $objModuloCpf->getCPF($customer->id);
       
        return $this->fillWith($params);
    }



    public function validate()
    {
        $cpfField = $this->getField('paggi_cpf');
      
        $numberDoc = preg_replace("/[^0-9]/", "", $cpfField->getValue());
        
        $objModuloCpf = Module::getInstanceByName('paggi');
        
        try {
           
            $objModuloCpf->cpfValidation($numberDoc); 
            
        } catch (Exception $exc) {

            $cpfField->addError($exc->getMessage());
        
        }


        return parent::validate();
    }


     public function submit(){
        $ok = parent::submit();
        if($ok){
            $cpfField = $this->getField('paggi_cpf');
            $numberDoc = preg_replace("/[^0-9]/", "", $cpfField->getValue());
            try{
            
                $context = Context::getContext();              
                PaggiCustomer::setCPFCustomerPS($context->customer, $numberDoc);

            }catch(Exception $ex){

                $ok = false;
                $cpfField->addError($ex->getMessage());
              
            }
            if (!$ok) {
                foreach ($this->customerPersister->getErrors() as $field => $errors) {
                    $this->formFields[$field]->setErrors($errors);
                }
            }
            return $ok;
        }
        return false;
    }
}
