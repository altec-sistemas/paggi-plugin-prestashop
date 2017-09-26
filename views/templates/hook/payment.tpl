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


<div class="row">
	<div class="col-xs-12">
		<p class="payment_module">
			<a class="paggi" href="{$link->getModuleLink('paggi', 'payment')|escape:'html':'UTF-8'}" title="{l s='Pay by Paggi' mod='paggi'}">
			<img src="{$this_path_bw}{$this_img}" alt="{l s='Pay by Paggi' mod='paggi'}" width="86"/>
				{l s='Pay by Paggi' mod='paggi'} <span>{l s='(order processing will be longer)' mod='paggi'}</span>
			</a>
		</p>
	</div>
</div>


<style type="text/css">
		
<!--
	p.payment_module a.paggi{
		padding: 33px 40px 34px 18px !important;
	}

     p.payment_module a.paggi:after{
     	    display: block;
		    content: "\f054";
		    position: absolute;
		    right: 15px;
		    margin-top: -11px;
		    top: 50%;
		    font-family: "FontAwesome";
		    font-size: 25px;
		    height: 22px;
		    width: 14px;
		    color: #777;
     }

-->


</style>
