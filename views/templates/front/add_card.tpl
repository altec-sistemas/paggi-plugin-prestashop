<div class="box">
	
	<form class="paggi" action="{$link->getModuleLink('paggi', 'card', [], true)|escape:'html'}" method="post"  >
		<div class="row">

			<div class="col-xs-12 col-sm-6">
				<h3 class="page-subheading">{l s='Add Card Paggi payment' mod='paggi'}</h3>

				
				<div class="form_content clearfix">				
					<div class="row">
						<div class="col-xs-12 col-sm-12">
							<div class="card-wrapper"></div>
						</div>
					</div>
					<br />
					<div class="row">
						<div class="col-xs-12 col-sm-8">
							<div class="form-group card_number">
						
								<label for="card_number">{l s='Credit card number'}</label>
								<div class="input-group">
										<span class="input-group-addon" 
										>

											<span class="icon icon-credit-card" aria-hidden="true"></span>
											


										</span>
								<input type="text" 
								placeholder="•••• •••• •••• ••••" 
								id="card_number"
								class="is_required validate form-control" name="PAGGI_CARD_NUMBER" />
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-sm-4">
							<div class="form-group">
							
								<label for="expiration">{l s='Card expiration'}</label>
									<div class="input-group">
										<span class="input-group-addon">
											<span class="icon icon-calendar" aria-hidden="true"></span>
										</span>
										<input type="text" id="expiration" 
										placeholder="•• / ••••" 
										size="7" 
										class="is_required validate form-control" name="PAGGI_CARD_EXPIRATE" />
								</div>
								
								
							</div>
						</div>
							

					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-12">
							<div class="form-group">
								<label for="cardalias_name">{l s='Card Alias'}</label>
								<div class="input-group">
												<span class="input-group-addon">
													<span class="icon icon-cog" aria-hidden="true"></span>
												</span>
								<input type="text" id="cardalias_name"
								class="is_required validate form-control" name="PAGGI_CARD_ALIAS" />
								</div>
							</div>
						</div>						

					</div>



					<div class="row">
						<div class="col-xs-12 col-sm-12">
							<div class="form-group">
								<label for="cardholder_name">{l s='Cardholder Name'}</label>
								<div class="input-group">
												<span class="input-group-addon">
													<span class="icon icon-user" aria-hidden="true"></span>
												</span>
								<input type="text" id="cardholder_name"
								class="is_required validate form-control" name="PAGGI_CARD_HOLDER_NAME" />
								</div>
							</div>
						</div>						

					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-8">
							<div class="form-group">
								<label for="document">{l s='Document'}</label>
								
								<input type="text" id="document"
								class="is_required validate form-control" name="PAGGI_CARD_DOCUMENT" />
								
							</div>
						</div>	

						<div class="col-xs-12 col-sm-4">
							<div class="form-group">
								<label for="cvc">{l s='CVC'}</label>
								<div class="input-group">
									<input type="text" 
									placeholder="•••" 
									id="cvc"
									class="is_required validate form-control" name="PAGGI_CARD_CVC" />
									<span class="input-group-addon">
										<span class="icon icon-question-sign" aria-hidden="true"></span>
									</span>
								</div>
								
							</div>
						</div>					

					</div>
					
					

					


					

					


					





					
				</div>
			</div>

			
		</div>
		<div class="row">
			<div class="col-xs-12 col-sm-12">
				<div class="submit">

					<button 
					class="btn btn-success button button-medium exclusive" 
					type="submit" 
					name="PAGGI_TASK_CARD" 
					value="PAGGI_OK"
					>
						<span>
							{l s='Add Card' mod='paggi'}
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

<script type="text/javascript">
    var mask_name = "{l s='Full Name'}";
    {literal}


    	$("form.paggi").card({
		    
		    container: '.card-wrapper', // *required*

		    formSelectors: {
		        numberInput: 'input[name=PAGGI_CARD_NUMBER]', // optional — default input[name="number"]
		        expiryInput: 'input[name=PAGGI_CARD_EXPIRATE]', // optional — default input[name="expiry"]
		        cvcInput: 'input[name=PAGGI_CARD_CVC]', // optional — default input[name="cvc"]
		        nameInput: 'input[name=PAGGI_CARD_HOLDER_NAME]' // optional - defaults input[name="name"]
		    },

		     messages: {
		        validDate: 'valid\ndate', // optional - default 'valid\nthru'
		        monthYear: 'mm/yyyy', // optional - default 'month/year'
		    },

		    // Default placeholders for rendered fields - optional
		    placeholders: {
		        number: '•••• •••• •••• ••••',
		        name: mask_name,
		        expiry: '••/••',
		        cvc: '•••'
		    },

		    
		});


    {/literal}
</script>
