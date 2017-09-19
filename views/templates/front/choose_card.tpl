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



<div class="box">
	
	<form class="paggi" action="{$link->getModuleLink('paggi', 'validation', [], true)|escape:'html'}" method="post"  >
		<div class="row">

			<div class="col-xs-12 col-sm-12">
				<h3 class="page-subheading">{l s='Paggi payment' mod='paggi'} - {l s='Choose the card to use'}</h3>

				<div class="form_content clearfix">				
					<div class="row">
						<div class="col-xs-12 col-sm-12">
							<div class="table-responsive">
							  <table class="table">
								  <thead>
								  <tr>
								  	<th> {l s='#'}</th>
								  	<th> {l s='Card type'}</th>
								  	<th> {l s='4 latest issues'}</th>
								  	<th> {l s='Expiration'}</th>						  	
								  	
								  	</tr>
								  </thead>

								  <tbody>
								  {if count($paggiCustomer->cards) > 0}

									  {foreach from=$paggiCustomer->cards item=card}
									  
									  	<tr>
									  		<td>
									  			<input type="radio" name="PAGGI_CHOOSE_CARD_ID" value="{$card['id']}">
									  		</td>
									  		<td>{$card['brand']}</td>
									  		<td>{$card['last4']}</td>
									  		<td>{$card['month']|str_pad:2:'0':$smarty.const.STR_PAD_LEFT} / {$card['year']}</td>
									  		
									  		
									  	</tr>
									  {/foreach}

								  {/if}
								  </tbody>
							  </table>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-12">
							<a  href="{$link->getModuleLink('paggi', 'card')|escape:'html'}"
							class="btn btn-primary button button-small" >
								<span>
									{l s='Add Card' mod='paggi'}
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>

			
		</div>
		<br />
		<div class="row">
						
						{assign var='sale_message' value={l s='%sx de %s - Total: %s'}}
						{assign var='sale_message_in_cast' value={l s='In cast - Total: %s'}}
						<div class="col-xs-12 col-sm-8">
							<div class="form-group">
								<label for="installments">{l s='Number of installments'}</label>
								<select 
								id="installments" 
								class="form-control" 
								name="PAGGI_NUMBER_INSTALLMENT" >

								{foreach from=$select_sales item=sale}

									{if $sale.installment == 1}

										<option value="{$sale.installment}">
											{$sale_message_in_cast|sprintf : {convertPrice price=$sale.total|floatval} }
											
										</option>

									{else}

										<option value="{$sale.installment}">
											{$sale_message|sprintf : $sale.installment : {convertPrice price=$sale.installment_amount|floatval} : {convertPrice price=$sale.total|floatval} }
										</option>

									{/if}
									
								
								{/foreach}
									
								</select>
								
							</div>
						</div>
							

					</div>

		<div class="row">
			<div class="col-xs-12 col-sm-12">
				<div class="submit">

					<button 
					class="btn btn-success button button-medium exclusive" 
					type="submit" 
					name="PAGGI_TASK" 
					value="PAGGI_CONFIRMED">
						<span>
							{l s='I confirm my order' mod='paggi'}
						</span>
					</button>

					


					<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="btn btn-default button button-medium">
						<span>
							{l s='Other payment methods' mod='paggi'}
						</span>
					</a>
				</div>
			</div>
		</div>
	</form>
	
</div>


{/if}
