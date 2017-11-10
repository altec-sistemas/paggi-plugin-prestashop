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
 * @category  PaymentModule
 * @package   Module
 * @author    Paggi <contact@paggi.com>
 * @copyright 2003-2017 Paggi
 * @license   http://opensource.org/licenses/afl-3.0.php
 *            Academic Free License (AFL 3.0)
 * @link      https://github.com/paggi-com/plugin-prestashop.git
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Paggi
 *
 * @category PaymentModule
 * @package  Module
 * @author   Paggi <contact@paggi.com>
 * @license  http://opensource.org/licenses/afl-3.0.php
 *           Academic Free License (AFL 3.0)
 * @link     https://github.com/paggi-com/plugin-prestashop.git
 */
class Paggi extends PaymentModule {

    protected $html = '';
    protected $postErrors = array();
    protected $env = 0;
    public $key = '';

    /**
     * This method constructor.
     */
    public function __construct() {

        //load Class
        require_once __DIR__ . '/classes/PaggiCustomer.php';

        $this->name = 'paggi';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.0';
        $this->author = 'Paggi';

        $this->controllers = array('payment', 'validation', 'card');

        $this->currencies = true;
        $this->is_eu_compatible = 1;

        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;


        //load variables
        $config = Configuration::getMultiple(
                        array(
                            'PAGGI_API_KEY_PRODUCTION',
                            'PAGGI_API_KEY_STAGING',
                            'PAGGI_ENVIRONMENT')
        );
        if (!empty($config['PAGGI_ENVIRONMENT'])) {
            $this->env = $config['PAGGI_ENVIRONMENT'];
        }

        if (!$this->env) {
            if (!empty($config['PAGGI_API_KEY_STAGING'])) {
                $this->key = $config['PAGGI_API_KEY_STAGING'];
                \Paggi\Paggi::setStaging(true);
            }
        } else {
            if (!empty($config['PAGGI_API_KEY_PRODUCTION'])) {
                $this->key = $config['PAGGI_API_KEY_PRODUCTION'];
                \Paggi\Paggi::setStaging(false);
            }
        }

        parent::__construct();

        $this->displayName = $this->l('Paggi');

        $this->description = $this->l('Paggi Payment Module');

        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        //warning access envirement staging
        if (!$this->env) {
            $this->adminDisplayWarning($this->l('You are in the Paggi development environment.'));
        }

        if (empty($this->key)) {
            $this->warning = $this->l('You need to set up an Api Key to use this module.');
        } else {
            //set init Api Key
            \Paggi\Paggi::setApiKey($this->key);
        }
    }

    /**
     * Install process
     *
     * @see    PaymentModule::install()
     * @return bool
     */
    public function install() {
        if (!parent::install() || !$this->registerHook('payment') 
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('paymentOption') 
            || !$this->registerHook('actionOrderHistoryAddAfter') || !PaggiCustomer::createTable()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall process
     *
     * @see    PaymentModule::uninstall()
     * @return bool
     */
    public function uninstall() {
        if (!parent::uninstall() || !PaggiCustomer::dropTable() || !Configuration::deleteByName('PAGGI_DOCUMENT_FIELD') || !Configuration::deleteByName('PAGGI_FREE_INSTALLMENTS') || !Configuration::deleteByName('PAGGI_MAX_INSTALLMENTS') || !Configuration::deleteByName('PAGGI_INTEREST_RATE') || !Configuration::deleteByName('PAGGI_API_KEY_PRODUCTION') || !Configuration::deleteByName('PAGGI_API_KEY_STAGING') || !Configuration::deleteByName('PAGGI_ENVIRONMENT') || !Configuration::deleteByName('PAGGI_IMG') || !Configuration::deleteByName('PAGGI_STATUS_APPROVED') || !Configuration::deleteByName('PAGGI_STATUS_DECLINED') || !Configuration::deleteByName('PAGGI_STATUS_REGISTERED') || !Configuration::deleteByName('PAGGI_STATUS_PRE_APPROVED') || !Configuration::deleteByName('PAGGI_STATUS_CLEARED') || !Configuration::deleteByName('PAGGI_STATUS_NOT_CLEARED') || !Configuration::deleteByName('PAGGI_STATUS_MANUAL_CLEARING') || !Configuration::deleteByName('PAGGI_STATUS_CAPTURED') || !Configuration::deleteByName('PAGGI_STATUS_CANCELLED') || !Configuration::deleteByName('PAGGI_STATUS_CHARGEBACK')
        ) {
            return false;
        }

        return true;
    }

    /**
     * To display the payment method.
     *
     * @param Object $params dataParams
     *
     * @see    http://doc.prestashop.com/display/PS16/Creating+a+payment+module
     * @return PaymentModule::display()
     */
    public function hookPayment($params) {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $this_path_ssl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/';
        $this->smarty->assign(
                array(
                    'this_path' => $this->_path,
                    'this_path_bw' => $this->_path,
                    'this_path_ssl' => $this_path_ssl,
                    'this_img' => $this->getPaggiImage(),
                )
        );

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentOptions($params) {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getEmbeddedPaymentOption($params),
        ];

        return $payment_options;
    }

    public function getEmbeddedPaymentOption($params) {
        $embeddedOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $embeddedOption->setCallToActionText($this->l('Payment by credit card'))
                ->setForm($this->generateForm($params))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation17', array(), true))
                ->setAdditionalInformation($this->context->smarty->fetch('module:paggi/views/templates/front/payment_infos.tpl'))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/' . $this->getPaggiImage()));

        return $embeddedOption;
    }

    protected function generateForm($params) {


        if (!empty($this->key)) {
            $cart = $params['cart'];

            $customer = new Customer($cart->id_customer);

            $cpf = $this->getCPF($customer->id);


            if (empty($cpf)) {

                $this->errors[] = Tools::displayError($this->l('CPF is empty.'));

                return $this->context->smarty->fetch('module:paggi/views/templates/front/not_config.tpl');
            }

            $paggiCustomer = PaggiCustomer::getLoadByCustomerPS($customer, $cpf);


            $this->context->smarty->assign(array(
                'paggiCustomer' => $paggiCustomer,
                'nbProducts' => $cart->nbProducts(),
                'cust_currency' => $cart->id_currency,
                'currencies' => $this->getCurrency((int) $cart->id_currency),
                'total' => $cart->getOrderTotal(true, Cart::BOTH),
                'this_path' => $this->getPathUri(),
                'this_path_bw' => $this->getPathUri(),
                'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
                'this_img' => $this->getPaggiImage(),
                'select_sales' => $this->getPaymentInstallments($cart->getOrderTotal(true, Cart::BOTH))
            ));

            return $this->context->smarty->fetch('module:paggi/views/templates/front/payment_form.tpl');
        } else {
            return $this->context->smarty->fetch('module:paggi/views/templates/front/not_config.tpl');
        }
    }

    /**
     * To display the payment confirmation.
     *
     * @param Object $params dataParams
     *
     * @see    http://doc.prestashop.com/display/PS16/Creating+a+payment+module
     * @return PaymentModule::display()
     */
    public function hookPaymentReturn($params) {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }



        if ($this->isPS17()) {

            $order = $params['order'];
            $total_paid = Tools::displayPrice($order->total_paid);
        } else {
            $order = $params['objOrder'];
            $total_paid = Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false);
        }

        $orderState = $order->getCurrentOrderState();

        if ($orderState->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign(array(
                'total_to_pay' => $total_paid,
                'status' => 'ok',
                'id_order' => $order->id,
                'orderState' => $orderState,
                'shop_name' => Configuration::get('PS_SHOP_NAME')
            ));

            if (isset($order->reference) && !empty($order->reference)) {
                $this->smarty->assign('reference', $order->reference);
            }
        } else {

            //condition for return message, where status 'ok' or 'failed'
            $this->smarty->assign('status', 'failed');
        }



        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function display($file, $dir, $cache = null, $content_id = null) {
        if ($this->isPS17()) {

            return $this->fetch('module:paggi/views/templates/hook/' . $dir);
        }

        return parent::display($file, $dir, $cache, $content_id);
    }

    /**
     * hook executed after changing purchase status
     *
     * @param Object $params dataParams
     *
     * @return void
     */
    public function hookActionOrderHistoryAddAfter($params) {
        $orderHistory = $params['order_history'];

        $order = new Order($orderHistory->id_order);

        $orderPayment = $order->getOrderPayments();

        try {

            $charge = \Paggi\Charge::findById($orderPayment[0]->transaction_id);

            $new_status = (int) Configuration::get('PAGGI_STATUS_' . strtoupper($charge->status));

            if (Configuration::get('PAGGI_STATUS_CAPTURED') == $orderHistory->id_order_state && $charge->status == 'manual_clearing') {
                $charge_captured = $charge->capture();

                $new_status = (int) Configuration::get('PAGGI_STATUS_' . strtoupper($charge_captured->status));
            } elseif (Configuration::get('PAGGI_STATUS_CANCELLED') == $orderHistory->id_order_state && ($charge->status == 'manual_clearing' || $charge->status == 'approved')) {

                $charge_cancel = $charge->cancel();
                $new_status = (int) Configuration::get('PAGGI_STATUS_' . strtoupper($charge_cancel->status));
            }
        } catch (\Paggi\PaggiException $ex) {

            $this->adminDisplayWarning($this->l('Internal error.'));

            PrestaShopLogger::addLog($ex->getMessage());

            $new_status = (int) Configuration::get('PS_OS_ERROR');
        }


        $orderHistory->id_order_state = (int) $new_status;

        if ($orderHistory->update()) {
            $order->current_state = $orderHistory->id_order_state;
            $order->update();
        }
    }

    /**
     * Check permission Currency.
     *
     * @param Cart $cart ClassCart
     *
     * @return bool
     */
    public function checkCurrency($cart) {
        $currency_order = new Currency((int) ($cart->id_currency));
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Calculate Installments
     *
     * @param Float $amount
     *
     * @return Array
     */
    public function getPaymentInstallments($amount) {
        $select = array();

        $free_installments = empty(Configuration::get("PAGGI_FREE_INSTALLMENTS")) ? 1 : Configuration::get("PAGGI_FREE_INSTALLMENTS");
        $max_installments = empty(Configuration::get("PAGGI_MAX_INSTALLMENTS")) ? 12 : Configuration::get("PAGGI_MAX_INSTALLMENTS");
        $interest_rate = empty(Configuration::get("PAGGI_INTEREST_RATE")) ? 0 : Configuration::get("PAGGI_INTEREST_RATE");

        for ($x = 1; $x <= $max_installments; $x++) {
            if ($x > $free_installments) {
                $amount_new = ($amount * $x * $interest_rate / 100) + $amount;
            } else {
                $amount_new = $amount;
            }

            $installment_amount = $amount_new / $x;

            $option = array(
                "installment" => $x,
                "total" => $amount_new,
                "installment_amount" => $installment_amount
            );

            array_push($select, $option);
        }


        return $select;
    }

    /**
     * Responsible for uploading Paggi Image
     *
     * @return void
     */
    protected function uploadImg() {
        $update_images_values = false;

        if (isset($_FILES['PAGGI_IMG']) && isset($_FILES['PAGGI_IMG']['tmp_name']) && !empty($_FILES['PAGGI_IMG']['tmp_name'])
        ) {
            if ($error = ImageManager::validateUpload($_FILES['PAGGI_IMG'], 4000000)) {
                return $error;
            } else {
                $ext = substr($_FILES['PAGGI_IMG']['name'], strrpos($_FILES['PAGGI_IMG']['name'], '.') + 1);
                $file_name = 'cartao.' . $ext;

                if (!move_uploaded_file($_FILES['PAGGI_IMG']['tmp_name'], dirname(__FILE__) . DIRECTORY_SEPARATOR . $file_name)) {
                    return $this->displayError($this->l('An error occurred while sending the file.'));
                } else {
                    if (Configuration::hasContext('PAGGI_IMG', null, Shop::getContext()) && Configuration::get('PAGGI_IMG') != $file_name
                    ) {
                        @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . Configuration::get('PAGGI_IMG'));
                    }

                    $values['PAGGI_IMG'] = $file_name;
                }
            }

            $update_images_values = true;
        }

        if ($update_images_values) {
            Configuration::updateValue('PAGGI_IMG', $values['PAGGI_IMG']);
        }
    }

    /**
     * Executing in method post
     *
     * @return void
     */
    protected function postProcess() {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('PAGGI_DOCUMENT_FIELD', Tools::getValue('PAGGI_DOCUMENT_FIELD'));
            Configuration::updateValue('PAGGI_FREE_INSTALLMENTS', Tools::getValue('PAGGI_FREE_INSTALLMENTS'));
            Configuration::updateValue('PAGGI_MAX_INSTALLMENTS', Tools::getValue('PAGGI_MAX_INSTALLMENTS'));
            Configuration::updateValue('PAGGI_INTEREST_RATE', Tools::getValue('PAGGI_INTEREST_RATE'));
            Configuration::updateValue('PAGGI_API_KEY_PRODUCTION', Tools::getValue('PAGGI_API_KEY_PRODUCTION'));
            Configuration::updateValue('PAGGI_API_KEY_STAGING', Tools::getValue('PAGGI_API_KEY_STAGING'));
            Configuration::updateValue('PAGGI_ENVIRONMENT', Tools::getValue('PAGGI_ENVIRONMENT'));
            Configuration::updateValue('PAGGI_STATUS_APPROVED', Tools::getValue('PAGGI_STATUS_APPROVED'));
            Configuration::updateValue('PAGGI_STATUS_DECLINED', Tools::getValue('PAGGI_STATUS_DECLINED'));
            Configuration::updateValue('PAGGI_STATUS_REGISTERED', Tools::getValue('PAGGI_STATUS_REGISTERED'));
            Configuration::updateValue('PAGGI_STATUS_PRE_APPROVED', Tools::getValue('PAGGI_STATUS_PRE_APPROVED'));
            Configuration::updateValue('PAGGI_STATUS_CLEARED', Tools::getValue('PAGGI_STATUS_CLEARED'));
            Configuration::updateValue('PAGGI_STATUS_NOT_CLEARED', Tools::getValue('PAGGI_STATUS_NOT_CLEARED'));
            Configuration::updateValue('PAGGI_STATUS_MANUAL_CLEARING', Tools::getValue('PAGGI_STATUS_MANUAL_CLEARING'));
            Configuration::updateValue('PAGGI_STATUS_CAPTURED', Tools::getValue('PAGGI_STATUS_CAPTURED'));
            Configuration::updateValue('PAGGI_STATUS_CANCELLED', Tools::getValue('PAGGI_STATUS_CANCELLED'));
            Configuration::updateValue('PAGGI_STATUS_CHARGEBACK', Tools::getValue('PAGGI_STATUS_CHARGEBACK'));
            Configuration::updateValue('PAGGI_CPF_FIELD_ACTIVED_MAPPED', Tools::getValue('PAGGI_CPF_FIELD_ACTIVED_MAPPED'));
            Configuration::updateValue('PAGGI_CPF_FIELD_TABLE_MAPPED', Tools::getValue('PAGGI_CPF_FIELD_TABLE_MAPPED'));
            Configuration::updateValue('PAGGI_CPF_FIELD_COLUMN_MAPPED', Tools::getValue('PAGGI_CPF_FIELD_COLUMN_MAPPED'));
            Configuration::updateValue('PAGGI_CPF_FIELD_FOREING_KEY_MAPPED', Tools::getValue('PAGGI_CPF_FIELD_FOREING_KEY_MAPPED'));



            if (Configuration::get('PAGGI_CPF_FIELD_ACTIVED_MAPPED') == 1) {


                $this->activeMappedNative();
            } else {

                $this->desactiveMappedNative();
            }


            $this->uploadImg();
        }
        $this->html .= $this->displayConfirmation($this->l('Updated settings.'));
    }

    public function isPS17() {

        return (version_compare(_PS_VERSION_, '1.7.0.0') >= 0);
    }

    public function activeMappedNative() {

        $this->desactiveMappedNative();

        $this->registerHook('displayHeader');


        if ($this->isPS17()) {

            if (!file_exists(_PS_OVERRIDE_DIR_ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'form')) {
                $oldumask = umask(0000);
                @mkdir(_PS_OVERRIDE_DIR_ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'form', 0755);
                umask($oldumask);
            }

            $this->addOverride("CustomerForm");

            $this->registerHook('additionalCustomerFormFields');
        } else {


            $this->registerHook('actionCustomerAccountAdd');

            $this->registerHook('displayCustomerIdentityForm');

            $this->registerHook('createAccountForm');

            $this->addOverride("IdentityController");

            $this->addOverride("AuthController");
        }
    }

    public function desactiveMappedNative() {

        $this->unregisterHook('displayHeader');

        $this->removeOverride("IdentityController");

        $this->removeOverride("AuthController");

        $this->unregisterHook('displayCustomerIdentityForm');

        $this->unregisterHook('createAccountForm');

        $this->unregisterHook('additionalCustomerFormFields');

        $this->unregisterHook('actionCustomerAccountAdd');

        $this->removeOverride("CustomerForm");
    }

    public function cpfValidation($item) {
        $nulos = array("12345678909", "11111111111", "22222222222", "33333333333",
            "44444444444", "55555555555", "66666666666", "77777777777",
            "88888888888", "99999999999", "00000000000");

        /* Retira todos os caracteres que nao sejam 0-9 */
        $cpf = preg_replace("/[^0-9]/", "", $item);

        if (strlen($cpf) <> 11) {
            throw new Exception($this->l('The CPF must contain 11 digits.'));
        }
        if (!is_numeric($cpf)) {
            throw new Exception($this->l('Only numbers are accepted.'));
        }

        /* Retorna falso se o cpf for nulo */
        if (in_array($cpf, $nulos)) {
            throw new Exception($this->l('Invalid CPF.'));
        }
        /* Calcula o penúltimo dígito verificador */
        $acum = 0;
        for ($i = 0; $i < 9; $i++) {
            $acum += $cpf[$i] * (10 - $i);
        }

        $x = $acum % 11;
        $acum = ($x > 1) ? (11 - $x) : 0;
        /* Retorna falso se o digito calculado eh diferente do passado na string */
        if ($acum != $cpf[9]) {
            throw new Exception($this->l('Invalid CPF. Please verify it and try again.'));
        }
        /* Calcula o último dígito verificador */
        $acum = 0;
        for ($i = 0; $i < 10; $i++) {
            $acum += $cpf[$i] * (11 - $i);
        }

        $x = $acum % 11;
        $acum = ($x > 1) ? (11 - $x) : 0;
        /* Retorna falso se o digito calculado eh diferente do passado na string */
        if ($acum != $cpf[10]) {
            throw new Exception($this->l('Invalid CPF. Please verify it and try again.'));
        }
    }

    public function hookDisplayHeader() {

        $controller_name = Tools::getValue('controller');
        $context = Context::getContext();
        $context->controller->addCss($this->_path . '/views/css/fontawesome/css/font-awesome.min.css');

        $context->controller->addJs($this->_path . 'views/js/payment.js');
        
        $context->controller->addJs($this->_path . 'views/js/jquery.mask.min.js');
        $context->controller->addJs($this->_path . 'views/js/cpf_hook.js');

        if ($controller_name == 'card') {
            $context->controller->addJs($this->_path . '/views/js/card.js');
            $context->controller->addJs($this->_path . '/views/js/jquery.card.js');
            $context->controller->addJs($this->_path . '/views/js/hook_card.js');
        }
    }

    public function hookcreateAccountForm() {

        return $this->hookDisplayCustomerIdentityForm();
    }

    /**
     * Hook for adding Customer Fields
     *
     * @param $params
     *
     * @return bool
     */
    public function hookAdditionalCustomerFormFields() {

        return array(
                    (new FormField)
                    ->setName('cpf')
                    ->setType('text')
                    ->setLabel(
                            $this->l('CPF')
                    )
                    ->addAvailableValue('placeholder', '000.000.000-00')
                    ->setRequired(true)
        );
    }

    /**
     * Hook executed after client's inclusion
     *
     * @param $params
     *
     * @return bool
     */
    public function hookActionCustomerAccountAdd($params) {
        $postData = $params['_POST'];

        $customer = $params['newCustomer'];

        $cpf = $postData['cpf'];

        $numberDoc = preg_replace("/[^0-9]/", "", $cpf);

        try {

            PaggiCustomer::setCPFCustomerPS($customer, $numberDoc);

            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function hookDisplayCustomerIdentityForm() {


        $this->context->controller->addJS($this->_path . 'views/js/jquery.mask.min.js');

        $cpf = PaggiCustomer::getCPFByCustomerPS($this->context->customer);

        $this->smarty->assign(array(
            'urlValidateDoc' => $this->context->link->getModuleLink('paggi', 'validatedoc'),
            'cpf' => $cpf
        ));

        return $this->display(__FILE__, 'blockcpf.tpl');
    }

    /**
     * Get Paggi Image Default or New
     *
     * @return string
     */
    public function getPaggiImage() {
        $image = empty(Configuration::get('PAGGI_IMG')) ? 'visa-mastercard.jpg' : Configuration::get('PAGGI_IMG');
        if (!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $image)) {
            $image = 'visa-mastercard.jpg';
        }

        return $image;
    }

    /**
     * Display administration form
     *
     * @return HelperForm
     */
    public function getContent() {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postProcess();
        } else {
            $this->html .= '<br />';
        }

        $this->html .= $this->renderForm();

        return $this->html;
    }

    /**
     * Prepare Context HelperForm
     *
     * @return HelperForm
     */
    public function renderForm() {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        //prepare view paggi image temp
        $image_file = $this->getPaggiImage();
        $ext = substr($image_file, strrpos($image_file, '.') + 1);
        $image = dirname(__FILE__) . DIRECTORY_SEPARATOR . $image_file;
        $image_url = ImageManager::thumbnail(
                        $image, $this->table . '_' . $this->name . '.' . $image_file, 350, strtolower($ext), true, true
        );

        $fields_form_configuration = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Image'),
                        'name' => 'PAGGI_IMG',
                        'image' => $image_url ? $image_url : false,
                        'display_image' => true,
                        'col' => 6,
                        'desc' => $this->l('Picture of checkout Paggi.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Api Key (Production)'),
                        'name' => 'PAGGI_API_KEY_PRODUCTION',
                        'size' => 36,
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Api Key (Development)'),
                        'name' => 'PAGGI_API_KEY_STAGING',
                        'desc' => $this->l('You can test our API using the following token: B31DCE74-E768-43ED-86DA-85501612548F. All transactions made with this key will be in demo mode and no value will be charged to any card.'),
                        'size' => 36,
                        'required' => false,
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Select the environment.'),
                        'name' => 'PAGGI_ENVIRONMENT',
                        'required' => true,
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Production'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Development'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form_installments = $this->getFieldsFormInstallments();

        $fields_form_field_mapping = $this->getFieldsFormFieldMapping();

        //load status prestashop
        $options_status = OrderState::getOrderStates($lang->id);

        $fields_form_status = $this->getFieldsFormStatus($options_status);

        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->table = $this->table;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form_configuration, $fields_form_installments, $fields_form_status, $fields_form_field_mapping));
    }

    public function getCPF($id_customer) {

        $cpf = '';

        if (Configuration::get('PAGGI_CPF_FIELD_ACTIVED_MAPPED') == 1) {


            $customer = new Customer($id_customer);
            $cpf = PaggiCustomer::getCPFByCustomerPS($customer);
        } else {

            if (!empty(Configuration::get('PAGGI_CPF_FIELD_TABLE_MAPPED')) && !empty(Configuration::get('PAGGI_CPF_FIELD_COLUMN_MAPPED')) && !empty(Configuration::get('PAGGI_CPF_FIELD_FOREING_KEY_MAPPED'))
            ) {
                $table_mapped = Configuration::get('PAGGI_CPF_FIELD_TABLE_MAPPED');
                $foreingkey_mapped = Configuration::get('PAGGI_CPF_FIELD_FOREING_KEY_MAPPED');
                $column_mapped = Configuration::get('PAGGI_CPF_FIELD_COLUMN_MAPPED');

                try {

                    $db_prefix = _DB_PREFIX_;
                    $sql = "SELECT * FROM `{$db_prefix}{$table_mapped}` WHERE `{$foreingkey_mapped }` = {$id_customer}";



                    $row = Db::getInstance()->getRow($sql);

                    $cpf = $row[$column_mapped];
                } catch (Exception $ex) {
                    PrestaShopLogger::addLog($ex->getMessage());

                    $this->adminDisplayWarning($this->l('An error occurred during the data mapping in the configuration.'));
                }
            }
        }

        return $cpf;
    }

    /**
     * Load Configuration General Variables
     *
     * @return Array
     */
    public function getConfigFieldsValues() {
        return array(
            'PAGGI_DOCUMENT_FIELD' => Tools::getValue('PAGGI_DOCUMENT_FIELD', Configuration::get('PAGGI_DOCUMENT_FIELD')),
            'PAGGI_FREE_INSTALLMENTS' => Tools::getValue('PAGGI_FREE_INSTALLMENTS', Configuration::get('PAGGI_FREE_INSTALLMENTS')),
            'PAGGI_MAX_INSTALLMENTS' => Tools::getValue('PAGGI_MAX_INSTALLMENTS', Configuration::get('PAGGI_MAX_INSTALLMENTS')),
            'PAGGI_INTEREST_RATE' => Tools::getValue('PAGGI_INTEREST_RATE', Configuration::get('PAGGI_INTEREST_RATE')),
            'PAGGI_API_KEY_PRODUCTION' => Tools::getValue('PAGGI_API_KEY_PRODUCTION', Configuration::get('PAGGI_API_KEY_PRODUCTION')),
            'PAGGI_API_KEY_STAGING' => Tools::getValue('PAGGI_API_KEY_STAGING', Configuration::get('PAGGI_API_KEY_STAGING')),
            'PAGGI_ENVIRONMENT' => Tools::getValue('PAGGI_ENVIRONMENT', Configuration::get('PAGGI_ENVIRONMENT')),
            'PAGGI_IMG' => Tools::getValue('PAGGI_IMG', Configuration::get('PAGGI_IMG')),
            'PAGGI_STATUS_APPROVED' => Tools::getValue('PAGGI_STATUS_APPROVED', Configuration::get('PAGGI_STATUS_APPROVED')),
            'PAGGI_STATUS_DECLINED' => Tools::getValue('PAGGI_STATUS_DECLINED', Configuration::get('PAGGI_STATUS_DECLINED')),
            'PAGGI_STATUS_REGISTERED' => Tools::getValue('PAGGI_STATUS_REGISTERED', Configuration::get('PAGGI_STATUS_REGISTERED')),
            'PAGGI_STATUS_PRE_APPROVED' => Tools::getValue('PAGGI_STATUS_PRE_APPROVED', Configuration::get('PAGGI_STATUS_PRE_APPROVED')),
            'PAGGI_STATUS_CLEARED' => Tools::getValue('PAGGI_STATUS_CLEARED', Configuration::get('PAGGI_STATUS_CLEARED')),
            'PAGGI_STATUS_NOT_CLEARED' => Tools::getValue('PAGGI_STATUS_NOT_CLEARED', Configuration::get('PAGGI_STATUS_NOT_CLEARED')),
            'PAGGI_STATUS_MANUAL_CLEARING' => Tools::getValue('PAGGI_STATUS_MANUAL_CLEARING', Configuration::get('PAGGI_STATUS_MANUAL_CLEARING')),
            'PAGGI_STATUS_CAPTURED' => Tools::getValue('PAGGI_STATUS_CAPTURED', Configuration::get('PAGGI_STATUS_CAPTURED')),
            'PAGGI_STATUS_CANCELLED' => Tools::getValue('PAGGI_STATUS_CANCELLED', Configuration::get('PAGGI_STATUS_CANCELLED')),
            'PAGGI_STATUS_CHARGEBACK' => Tools::getValue('PAGGI_STATUS_CHARGEBACK', Configuration::get('PAGGI_STATUS_CHARGEBACK')),
            'PAGGI_CPF_FIELD_ACTIVED_MAPPED' => Tools::getValue('PAGGI_CPF_FIELD_ACTIVED_MAPPED', Configuration::get('PAGGI_CPF_FIELD_ACTIVED_MAPPED')),
            'PAGGI_CPF_FIELD_TABLE_MAPPED' => Tools::getValue('PAGGI_CPF_FIELD_TABLE_MAPPED', Configuration::get('PAGGI_CPF_FIELD_TABLE_MAPPED')),
            'PAGGI_CPF_FIELD_COLUMN_MAPPED' => Tools::getValue('PAGGI_CPF_FIELD_COLUMN_MAPPED', Configuration::get('PAGGI_CPF_FIELD_COLUMN_MAPPED')),
            'PAGGI_CPF_FIELD_FOREING_KEY_MAPPED' => Tools::getValue('PAGGI_CPF_FIELD_FOREING_KEY_MAPPED', Configuration::get('PAGGI_CPF_FIELD_FOREING_KEY_MAPPED'))
        );
    }

    /**
     * Load Configuration Status Variables
     *
     * @return Array
     */
    public function getFieldsFormStatus($options_status) {
        $fields_form_status = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Transaction status'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Approved'),
                        'desc' => $this->l('Transaction successfully sent to the acquirer.'),
                        'name' => 'PAGGI_STATUS_APPROVED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Declined'),
                        'desc' => $this->l('Transaction declined by buyer'),
                        'name' => 'PAGGI_STATUS_DECLINED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Registered:'),
                        'desc' => $this->l('Transaction saved to system but not sent to buyer or risk analysis'),
                        'name' => 'PAGGI_STATUS_REGISTERED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Pre Approved'),
                        'desc' => $this->l('Transaction authorized by buyer but not confirmed'),
                        'name' => 'PAGGI_STATUS_PRE_APPROVED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Approved credit'),
                        'desc' => $this->l('Transaction authorized by the risk analysis but not sent to the acquirer'),
                        'name' => 'PAGGI_STATUS_CLEARED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('At risk'),
                        'desc' => $this->l('Transaction declined by risk analysis'),
                        'name' => 'PAGGI_STATUS_NOT_CLEARED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Manual Cleared'),
                        'desc' => $this->l('Pre-approved transaction for review.'),
                        'name' => 'PAGGI_STATUS_MANUAL_CLEARING',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Captured'),
                        'desc' => $this->l('Pre-authorized transaction was confirmed with the acquirer'),
                        'name' => 'PAGGI_STATUS_CAPTURED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Cancelled'),
                        'desc' => $this->l('Request for cancellation was sent to the buyer (confirmation within 7 days)'),
                        'name' => 'PAGGI_STATUS_CANCELLED',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Chargeback'),
                        'desc' => $this->l('Transaction not recognized by cardholder.'),
                        'name' => 'PAGGI_STATUS_CHARGEBACK',
                        'options' => array(
                            'query' => $options_status,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );

        return $fields_form_status;
    }

    /**
     * Load Configuration Installments
     *
     * @return Array
     */
    public function getFieldsFormInstallments() {
        $options = array();

        for ($i = 1; $i <= 12; $i ++) {
            array_push($options, array(
                'id' => $i, // The value of the 'value' attribute of the <option> tag.
                'name' => $i . ' ' . $this->l('plots')             // The value of the text content of the  <option> tag.
            ));
        }



        $fields_form_status = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Installment Settings'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Interest free parcelling'),
                        'desc' => $this->l('Number of installments without interest'),
                        'name' => 'PAGGI_FREE_INSTALLMENTS',
                        'options' => array(
                            'query' => $options, // $options contains the data itself.
                            'id' => 'id', // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                            'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Maximum number of plots'),
                        'desc' => $this->l('Maximum number of parcels possible with payment by credit card.'),
                        'name' => 'PAGGI_MAX_INSTALLMENTS',
                        'options' => array(
                            'query' => $options, // $options contains the data itself.
                            'id' => 'id', // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                            'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Interest Rate'),
                        'desc' => $this->l('Interest rate value.'),
                        'name' => 'PAGGI_INTEREST_RATE'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );

        return $fields_form_status;
    }

    /**
     * Load Configuration Field Mapping
     *
     * @return Array
     */
    public function getFieldsFormFieldMapping() {
        $fields_form_status = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Map CPF field'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use CPF configuration of Paggi native module.'),
                        'name' => 'PAGGI_CPF_FIELD_ACTIVED_MAPPED',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name of the table to be mapped'),
                        'name' => 'PAGGI_CPF_FIELD_TABLE_MAPPED'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Column to be mapped'),
                        'name' => 'PAGGI_CPF_FIELD_COLUMN_MAPPED'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Foreign key customer'),
                        'name' => 'PAGGI_CPF_FIELD_FOREING_KEY_MAPPED',
                        'desc' => $this->l('Foreign key with prestashop id_customer. Ex.: `id_mymodule_customer`')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );

        return $fields_form_status;
    }

}
