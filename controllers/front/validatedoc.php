<?php

/**
 * Description of validatedoc
 *
 * @author Ederson Ferreira <ederson.dev@gmail.com>
 */
class PaggiValidateDocModuleFrontController extends ModuleFrontController {

    public function initContent() {
        $cpf = filter_input(INPUT_GET, 'cpf');
        $inputCpf = preg_replace("/[^0-9]/", "", $cpf);


        $arrRetorno = array(
            'status' => false
        );

        try {

            $this->module->cpfValidation($inputCpf);

            $arrRetorno['status'] = true;
        } catch (Exception $exc) {

            $arrRetorno['error'] = $exc->getMessage();
        }

        echo Tools::jsonEncode($arrRetorno);

        exit;
    }

}
