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
 * Class PaggiPaymentModuleFrontController
 *
 * @category ModuleFrontController
 * @package  Module
 * @author   Paggi <contact@paggi.com>
 * @license  http://opensource.org/licenses/afl-3.0.php
 *           Academic Free License (AFL 3.0)
 * @link     https://github.com/paggi-com/plugin-prestashop.git
 */

class PaggiPaymentModuleFrontController extends ModuleFrontController
{
    protected $paggiCustomer;

    /**
     * Init Content Display Payment
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

        if (!empty($this->module->key)) {
            $this->paggiCustomer = PaggiCustomer::getLoadByCustomerPS($customer);

            $this->displayChooseCard();
        } else {
            $this->displayNotConfig();
        }
    }


    protected function displayChooseCard()
    {
        $cart = $this->context->cart;
        //passed data as parameter to the template
        $this->context->smarty->assign(array(
            'paggiCustomer' => $this->paggiCustomer,
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int) $cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
            'this_img' => $this->module->getPaggiImage(),
            'select_sales'=> $this->module->getPaymentInstallments($cart->getOrderTotal(true, Cart::BOTH))
        ));

        $this->setTemplate('choose_card.tpl');
    }


    protected function displayNotConfig()
    {
        $this->setTemplate('not_config.tpl');
    }
}
