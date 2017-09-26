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
 * @category  ObjectModel
 * @package   Module\Classes
 * @author    Paggi <contact@paggi.com>
 * @copyright 2003-2017 Paggi
 * @license   http://opensource.org/licenses/afl-3.0.php
 *            Academic Free License (AFL 3.0)
 * @link      https://github.com/paggi-com/plugin-prestashop.git
 * International Registered Trademark & Property of PrestaShop SA
 */


require_once __DIR__.'/../lib/autoload.php';

/**
 * Class PaggiPaymentModuleFrontController
 *
 * @category ObjectModel
 * @package  Module\Classes
 * @author   Paggi <contact@paggi.com>
 * @license  http://opensource.org/licenses/afl-3.0.php
 *           Academic Free License (AFL 3.0)
 * @link     https://github.com/paggi-com/plugin-prestashop.git
 */

class PaggiCustomer extends ObjectModel
{
    public $id;
    public $id_customer_paggi;
    public $id_customer_ps;
    public $name;
    public $phone;
    public $email;
    public $description;
    public $created;

    /**
     * Definifion columns table
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'paggi_customer',
        'primary' => 'id',
        'fields' => array(
            'id_customer_paggi' =>  array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
             'id_customer_ps' =>  array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
           
        ),
    );


    /**
     * Create table struct for working
     *
     * @return Bool
     */
    public static function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paggi_customer`(
        `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_customer_paggi` varchar(255) NOT NULL,
          `id_customer_ps` int(10) UNSIGNED NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
        ALTER TABLE `'._DB_PREFIX_.'paggi_customer` ADD CONSTRAINT `fk_customerpaggi_customerprestashop` FOREIGN KEY ( `id_customer` ) REFERENCES `'._DB_PREFIX_.'customer` ( ` id_customer ` );
        ';


        return Db::getInstance()->execute($sql);
    }

    /**
     * Drop table struct
     *
     * @return Bool
     */
    public static function dropTable()
    {
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'paggi_customer`';

        return Db::getInstance()->execute($sql);
    }


    /**
     * Load data Paggi Customer
     *
     * @param Mixed $customer Customer
     *
     * @return Json
     */
    public static function getLoadByCustomerPS($customer)
    {   

        $field_document = Configuration::get('PAGGI_DOCUMENT_FIELD');

        //select data paggiCustomer
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'paggi_customer` 
        WHERE `id_customer_ps` = \''.$customer->id.'\'';

        $result = Db::getInstance()->executeS($sql);

        if (count($result) == 0) {
            $params = array(
                'name' =>$customer->firstname.' '.$customer->lastname,
                'email' => $customer->email,
                'document' =>$customer->$field_document
            );

            $customer_paggi = \Paggi\Customer::create($params);

            $paggiCustomer = new PaggiCustomer();
            $paggiCustomer->id_customer_ps = $customer->id;
            $paggiCustomer->id_customer_paggi = $customer_paggi->id;

            $paggiCustomer->save();
        } else {
            $paggiCustomer = new PaggiCustomer($result[0]['id']);
        }

        $customer_paggi = \Paggi\Customer::FindById($paggiCustomer->id_customer_paggi);

        return $customer_paggi;
    }
}
