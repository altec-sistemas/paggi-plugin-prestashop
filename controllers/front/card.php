<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @category    PaymentModule
 * @package     Module
 * @author      Paggi <contact@paggi.com>
 * @copyright   2003-2017 Paggi
 * @license     http://opensource.org/licenses/afl-3.0.php
 *              Academic Free License (AFL 3.0)
 * @link        https://github.com/paggi-com/plugin-prestashop.git
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaggiCardsModuleFrontController
 *
 * @category ModuleFrontController
 * @package  Module
 * @author   Paggi <contact@paggi.com>
 * @license  http://opensource.org/licenses/afl-3.0.php
 *           Academic Free License (AFL 3.0)
 * @link     https://github.com/paggi-com/plugin-prestashop.git
 */

class PaggiCardModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        parent::postProcess();

        if (!Tools::isSubmit('PAGGI_TASK_CARD')) {
            return;
        }

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'paggi') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'paggi'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        //if function delete card
        if (Tools::getValue('PAGGI_TASK_CARD') == "DELETE_CARD") {
            $this->ajaxDeleteCard();
        }
        

        $card_number = Tools::getValue('PAGGI_CARD_NUMBER');
        $card_alias = Tools::getValue('PAGGI_CARD_ALIAS');
        $card_expiration = explode("/", Tools::getValue('PAGGI_CARD_EXPIRATE'));
        $card_document = Tools::getValue('PAGGI_CARD_DOCUMENT');
        $card_cvc = Tools::getValue('PAGGI_CARD_CVC');
        $card_installment = Tools::getValue('PAGGI_NUMBER_INSTALLMENT');

        $card_month = trim($card_expiration[0]);
        $card_year = trim($card_expiration[1]);

        $cpf = $this->module->getCPF($customer->id);
        $paggiCustomer = PaggiCustomer::getLoadByCustomerPS($customer, $cpf);

        //params create card
        $params = array(
          'customer_id' => $paggiCustomer->id,
          'name' => Configuration::get('PS_SHOP_NAME'),
          'number' => $card_number,
          'month' => $card_month ,
          'year' =>$card_year,
          'cvc' =>$card_cvc,
          'card_alias' => $card_alias,
          'validate' => true
         );

        try {
            $card_paggi = \Paggi\Card::create($params);

            Tools::redirect(Context::getContext()->link->getModuleLink('paggi', 'payment'));
        } catch (\Paggi\PaggiException $ex) {
            $message = Tools::jsonDecode($ex->getMessage());

            foreach ($message->errors as $error) {
                $this->errors[] = $error->message;
            }


           
            //die($ex);
        }
    }

    /**
     * Init Content Display
     *
     * @see FrontController::initContent()
     * @return void
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }


        $this->addJs(_MODULE_DIR_.$this->module->name.'/views/js/card.js');
        $this->addJs(_MODULE_DIR_.$this->module->name.'/views/js/jquery.card.js');
      
        
        $this->displayAddCard();
    }


    public function ajaxDeleteCard()
    {
        $id_card = Tools::getValue("PAGGI_CARD_ID");

        $card = \Paggi\Card::findById($id_card);

        $response = array(
            "status"=>false,
            "message"=> $this->module->l('Could not delete credit card.', 'paggi')
        );

        if ($card->delete()) {
            $response = array(
                "status"=>true,
                "message"=> $this->module->l('Credit card successfully deleted', 'paggi')
            );
        }

        $json = Tools::jsonEncode($response);

        header('Content-Type: application/json');
        $this->ajaxDie($json);
    }


    public function displayAddCard()
    {
        $cart = $this->context->cart;
        //passed data as parameter to the template
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
            'this_img' => $this->module->getPaggiImage(),
            'errors' => $this->errors
        ));

        $this->setTemplate('add_card.tpl');
    }
}
