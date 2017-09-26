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

class Paggi extends PaymentModule
{
    protected $html = '';

    protected $postErrors = array();

    protected $env = 0;

    protected $key = '';

    /**
     * This method constructor.
     */
    public function __construct()
    {

        //load Class
        require_once __DIR__.'/classes/PaggiCustomer.php';

        $this->name = 'paggi';

        $this->tab = 'payments_gateways';
        $this->version = '1.0.2';
        $this->author = 'PrestaShop';

        $this->controllers = array('payment', 'validation', 'card');

        $this->currencies = true;
        $this->is_eu_compatible = 1;

        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;


        //load variables
        $config = Configuration::getMultiple(
            array(
                'PAGGI_API_KEY_PRODUCTION',
                'PAGGI_API_KEY_TEST',
                'PAGGI_ENVIRONMENT')
        );
        if (!empty($config['PAGGI_ENVIRONMENT'])) {
            $this->env = $config['PAGGI_ENVIRONMENT'];
        }

        if (!$this->env) {
            if (!empty($config['PAGGI_API_KEY_TEST'])) {
                $this->key = $config['PAGGI_API_KEY_TEST'];
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

        //warning access envirement test
        if (!$this->env) {
            $this->adminDisplayWarning($this->l('You are in a test environment this module.'));
        }

        if (empty($this->key)) {
            $this->warning = $this->l('Api Key must be configured to use this module.');
        }else{
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
    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('paymentReturn')
            || !PaggiCustommer::createTable()
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
    public function uninstall()
    {
        if (!parent::uninstall()
            || !PaggiCustommer::dropTable()
            || !Configuration::deleteByName('PAGGI_API_KEY_PRODUCTION')
            || !Configuration::deleteByName('PAGGI_API_KEY_TEST')
            || !Configuration::deleteByName('PAGGI_ENVIRONMENT')
            || !Configuration::deleteByName('PAGGI_IMG')
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
    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $this_path_ssl = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/';
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

    /**
     * To display the payment confirmation.
     *
     * @param Object $params dataParams
     *
     * @see    http://doc.prestashop.com/display/PS16/Creating+a+payment+module
     * @return PaymentModule::display()
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        //condition for return message, where status 'ok' or 'failed'
        $this->smarty->assign('status', 'ok');

        return $this->display(__FILE__, 'payment_return.tpl');
    }


    /**
     * Check permission Currency.
     *
     * @param Cart $cart ClassCart
     *
     * @return bool
     */
    public function checkCurrency($cart)
    {
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
    public function getPaymentInstallments($amount)
    {
        $select = array();

        $free_installments = empty(Configuration::get("PAGGI_FREE_INSTALLMENTS")) ? 1 : Configuration::get("PAGGI_FREE_INSTALLMENTS");
        $max_installments =  empty(Configuration::get("PAGGI_MAX_INSTALLMENTS")) ? 12 : Configuration::get("PAGGI_MAX_INSTALLMENTS");
        $interest_rate =  empty(Configuration::get("PAGGI_INTEREST_RATE")) ? 0 : Configuration::get("PAGGI_INTEREST_RATE");

        for ($x = 1; $x <= $max_installments; $x++) {
            if ($x > $free_installments) {
                $amount_new = ($amount * $x * $interest_rate / 100) + $amount;
            } else {
                $amount_new = $amount;
            }

            $installment_amount =  $amount_new / $x;

            $option = array(
            "installment" => $x,
            "total" => $amount_new,
            "installment_amount"=> $installment_amount
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
    protected function uploadImg()
    {
        $update_images_values = false;

        if (isset($_FILES['PAGGI_IMG'])
            && isset($_FILES['PAGGI_IMG']['tmp_name'])
            && !empty($_FILES['PAGGI_IMG']['tmp_name'])
        ) {
            if ($error = ImageManager::validateUpload($_FILES['PAGGI_IMG'], 4000000)) {
                return $error;
            } else {
                $ext = substr($_FILES['PAGGI_IMG']['name'], strrpos($_FILES['PAGGI_IMG']['name'], '.') + 1);
                $file_name = 'cartao.'.$ext;

                if (!move_uploaded_file($_FILES['PAGGI_IMG']['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.$file_name)) {
                    return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                } else {
                    if (Configuration::hasContext('PAGGI_IMG', null, Shop::getContext())
                        && Configuration::get('PAGGI_IMG') != $file_name
                    ) {
                        @unlink(dirname(__FILE__).DIRECTORY_SEPARATOR.Configuration::get('PAGGI_IMG'));
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
    protected function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('PAGGI_API_KEY_PRODUCTION', Tools::getValue('PAGGI_API_KEY_PRODUCTION'));
            Configuration::updateValue('PAGGI_API_KEY_TEST', Tools::getValue('PAGGI_API_KEY_TEST'));
            Configuration::updateValue('PAGGI_ENVIRONMENT', Tools::getValue('PAGGI_ENVIRONMENT'));
            Configuration::updateValue('PAGGI_STATUS_APPROVED' , Tools::getValue('PAGGI_STATUS_APPROVED' ));
            Configuration::updateValue('PAGGI_STATUS_DECLINED' , Tools::getValue('PAGGI_STATUS_DECLINED' ));
            Configuration::updateValue('PAGGI_STATUS_REGISTERED', Tools::getValue('PAGGI_STATUS_REGISTERED'));
            Configuration::updateValue('PAGGI_STATUS_PRE_APPROVED', Tools::getValue('PAGGI_STATUS_PRE_APPROVED'));
            Configuration::updateValue('PAGGI_STATUS_CLEARED', Tools::getValue('PAGGI_STATUS_CLEARED'));
            Configuration::updateValue('PAGGI_STATUS_NOT_CLEARED' , Tools::getValue('PAGGI_STATUS_NOT_CLEARED' ));
            Configuration::updateValue('PAGGI_STATUS_MANUAL_CLEARED', Tools::getValue('PAGGI_STATUS_MANUAL_CLEARED'));
            Configuration::updateValue('PAGGI_STATUS_CAPTURED', Tools::getValue('PAGGI_STATUS_CAPTURED'));
            Configuration::updateValue('PAGGI_STATUS_CAPTURED' , Tools::getValue('PAGGI_STATUS_CAPTURED' ));
            Configuration::updateValue('PAGGI_STATUS_CHARGEBACK', Tools::getValue('PAGGI_STATUS_CHARGEBACK'));

            $this->uploadImg();
        }
        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    /**
     * Get Paggi Image Default or New
     *
     * @return string
     */
    public function getPaggiImage()
    {
        $image = empty(Configuration::get('PAGGI_IMG')) ? 'logo.png' : Configuration::get('PAGGI_IMG');
        if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$image)) {
            $image = 'logo.png';
        }

        return $image;
    }

    /**
     * Display administration form
     *
     * @return HelperForm
     */
    public function getContent()
    {
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
    public function renderForm()
    {

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        //prepare view paggi image temp
        $image_file = $this->getPaggiImage();
        $ext = substr($image_file, strrpos($image_file, '.') + 1);
        $image = dirname(__FILE__).DIRECTORY_SEPARATOR.$image_file;
        $image_url = ImageManager::thumbnail(
            $image,
            $this->table.'_'.$this->name.'.'.$image_file,
            350,
            strtolower($ext),
            true,
            true
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
                        'label' => $this->l('Paggi image'),
                        'name' => 'PAGGI_IMG',
                        'image' => $image_url ? $image_url : false,
                        'display_image' => true,
                        'col' => 6,
                        'desc' => $this->l('Upload a paggi image from your computer.'),
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
                        'label' => $this->l('Api Key (Test)'),
                        'name' => 'PAGGI_API_KEY_TEST',
                        'desc' => $this->l('You can test out our API using this key: B31DCE74-E768-43ED-86DA-85501612548F, even before you create an account with us! All charges made with this key will be in demonstration mode an will not charge any card!'),
                        'size' => 36,
                        'required' => false,
                    ),
                    array(
                      'type' => 'radio',
                      'label' => $this->l('Enable this option Enverioment'),
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
                              'label' => $this->l('Test'),
                            ),
                      ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );


       

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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form_configuration, $fields_form_status));
    }

    /**
     * Load Configuration Variables
     *
     * @return Array
     */
    public function getConfigFieldsValues()
    {
        return array(
          'PAGGI_API_KEY_PRODUCTION' => Tools::getValue('PAGGI_API_KEY_PRODUCTION', Configuration::get('PAGGI_API_KEY_PRODUCTION')),
          'PAGGI_API_KEY_TEST' => Tools::getValue('PAGGI_API_KEY_TEST', Configuration::get('PAGGI_API_KEY_TEST')),
          'PAGGI_ENVIRONMENT' => Tools::getValue('PAGGI_ENVIRONMENT', Configuration::get('PAGGI_ENVIRONMENT')),
          'PAGGI_IMG' => Tools::getValue('PAGGI_IMG', Configuration::get('PAGGI_IMG')),
          'PAGGI_STATUS_APPROVED' => Tools::getValue('PAGGI_STATUS_APPROVED', Configuration::get('PAGGI_STATUS_APPROVED')),
          'PAGGI_STATUS_DECLINED' => Tools::getValue('PAGGI_STATUS_DECLINED', Configuration::get('PAGGI_STATUS_DECLINED')),
          'PAGGI_STATUS_REGISTERED' => Tools::getValue('PAGGI_STATUS_REGISTERED', Configuration::get('PAGGI_STATUS_REGISTERED')),
          'PAGGI_STATUS_PRE_APPROVED' => Tools::getValue('PAGGI_STATUS_PRE_APPROVED', Configuration::get('PAGGI_STATUS_PRE_APPROVED')),
          'PAGGI_STATUS_CLEARED' => Tools::getValue('PAGGI_STATUS_CLEARED', Configuration::get('PAGGI_STATUS_CLEARED')),
          'PAGGI_STATUS_NOT_CLEARED' => Tools::getValue('PAGGI_STATUS_NOT_CLEARED', Configuration::get('PAGGI_STATUS_NOT_CLEARED')),
          'PAGGI_STATUS_MANUAL_CLEARED' => Tools::getValue('PAGGI_STATUS_MANUAL_CLEARED', Configuration::get('PAGGI_STATUS_MANUAL_CLEARED')),
          'PAGGI_STATUS_CAPTURED' => Tools::getValue('PAGGI_STATUS_CAPTURED', Configuration::get('PAGGI_STATUS_CAPTURED')),
          'PAGGI_STATUS_CAPTURED' => Tools::getValue('PAGGI_STATUS_CAPTURED', Configuration::get('PAGGI_STATUS_CAPTURED')),
          'PAGGI_STATUS_CHARGEBACK' => Tools::getValue('PAGGI_STATUS_CHARGEBACK', Configuration::get('PAGGI_STATUS_CHARGEBACK'))
        );
    }



    public function getFieldsFormStatus($options_status){
       $fields_form_status = array(
           'form' => array(
                'legend' => array(
                    'title' => $this->l('Transaction status'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                   
                   array(
                      'type' => 'select',                              
                      'label' => $this->l('Approved:'),         
                      'desc' => $this->l('Charge captured for acquirer'),  
                      'name' => 'PAGGI_STATUS_APPROVED',
                      'options' => array(
                        'query' => $options_status,                       
                        'id' => 'id_order_state',                           
                        'name' => 'name'                           
                      )
                    ),

                   array(
                      'type' => 'select',                              
                      'label' => $this->l('Declined:'),         
                      'desc' => $this->l('Charge declined for acquirer'),  
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
                      'desc' => $this->l('Charge registered but not captured'),
                      'name' => 'PAGGI_STATUS_REGISTERED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),
                    array(
                      'type' => 'select',                              
                      'label' => $this->l('Pre Approved:'),         
                      'desc' => $this->l('Pre-authorization created in acquirer'),
                      'name' => 'PAGGI_STATUS_PRE_APPROVED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),

                    array(
                      'type' => 'select',                              
                      'label' => $this->l('Cleared:'),         
                      'desc' => $this->l('Charge passing from the risk analysis'),
                      'name' => 'PAGGI_STATUS_CLEARED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),

                    array(
                      'type' => 'select',                              
                      'label' => $this->l('Not Cleared:'),         
                      'desc' => $this->l('Charge declined from risk analysis'),
                      'name' => 'PAGGI_STATUS_NOT_CLEARED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),

                     array(
                      'type' => 'select',                              
                      'label' => $this->l('Manual Cleared:'),         
                      'desc' => $this->l('Charge should be manually reviewed'),
                      'name' => 'PAGGI_STATUS_MANUAL_CLEARED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),


                     array(
                      'type' => 'select',                              
                      'label' => $this->l('Captured:'),         
                      'desc' => $this->l('Pre-authorization captured in acquirer'),
                      'name' => 'PAGGI_STATUS_CAPTURED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),

                    array(
                      'type' => 'select',                              
                      'label' => $this->l('Cancelled:'),         
                      'desc' => $this->l('Charge cancelled'),
                      'name' => 'PAGGI_STATUS_CAPTURED',
                      'options' => array(
                        'query' => $options_status,
                        'id' => 'id_order_state',
                        'name' => 'name'
                      )
                    ),

                    array(
                      'type' => 'select',                              
                      'label' => $this->l('Chargeback:'),         
                      'desc' => $this->l('Chargeback from customer or bank'),
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
}
