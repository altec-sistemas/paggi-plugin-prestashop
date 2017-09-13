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



{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='paggi'}">{l s='Checkout' mod='paggi'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Paggi payment' mod='paggi'}
{/capture}



<h2>{l s='Order summary' mod='paggi'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='paggi'}</p>
{else}

<h3>{l s='Paggi payment' mod='paggi'}</h3>
<form action="{$link->getModuleLink('paggi', 'validation', [], true)|escape:'html'}" method="post">


	<p>
		<img src="{$this_path_bw}{$this_img}" alt="{l s='Bank wire' mod='paggi'}" style="float:left; margin: 0px 10px 5px 0px;" />
	</p>

	<br />
	<p class="cart_navigation" id="cart_navigation">
		<input type="submit" value="{l s='I confirm my order' mod='paggi'}" class="exclusive_large" />
		<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='paggi'}</a>
	</p>
</form>
{/if}
