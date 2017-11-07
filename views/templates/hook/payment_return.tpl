{*
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
*  @author Paggi <contact@paggi.com>
*  @copyright  2003-2017 Paggi
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $status == 'ok'}
    <p>
        {l s='Your order on %s is complete.'|sprintf:$shop_name mod='paggi'}
        <br /><br />
        {l s='The current status of your order is "%s"' sprintf=$orderState->name mod='paggi'}

        <br /><br />- {l s='Amount' mod='paggi'} <span class="price"><strong>{$total_to_pay}</strong></span>

        {if !isset($reference)}
            <br /><br />- {l s='The reference number of this purchase is #%d.' sprintf=$id_order mod='paggi'}
        {/if}		<br /><br />
        {l s='If you have questions, comments or concerns, please contact our' mod='paggi'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='paggi'}</a>.
    </p>
{else}
    <p class="warning">
        {l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='paggi'} 
        <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='paggi'}</a>.
    </p>
{/if}
