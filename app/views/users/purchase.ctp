
<!-- Stripe Setup -->
<?
	$this->additionalScripts = array('https://js.stripe.com/v1/');
?>


<script type="text/javascript">
	// this identifies your website in the createToken call below
	Stripe.setPublishableKey('<? echo STRIPE_PUBLISHABLE_KEY; ?>');
	
	$(document).ready(function() {
		
		<? if(!isset($payment) || !$payment){ ?>
		
			// Payments
			$("#LoginForm").submit(function(event) {
				// disable the submit button to prevent repeated clicks
				$('[type=submit]').attr("disabled", "disabled");

				var amount = <? echo $plan['cost_in_cents'] ?>; // amount to charge in cents
				Stripe.createToken({
					number: $('.card-number').val(),
					cvc: $('.card-cvc').val(),
					exp_month: $('.card-expiry-month').val(),
					exp_year: $('.card-expiry-year').val()
				}, stripeResponseHandler);

				// rename the button
				$('[type=submit]').val('Submitting, Please Wait');

				// prevent the form from submitting with the default action
				return false;
				});

			
		<? } ?>

	});
	
	// Stripe response
	// - appending token to form
	function stripeResponseHandler(status, response) {
		if (response.error) {
			//show the errors on the form
			$(".payment-errors").html('Payment error: ' + response.error.message);
			alert('Payment error: ' + response.error.message);
			// re-enable the submit button
			$('[type=submit]').removeAttr("disabled");
			$('[type=submit]').val('Try Again, Sign Up');
		} else {
			var form$ = $('#LoginForm');
			// token contains id, last4, and card type
			var token = response['id'];
			// insert the token into the form so it gets submitted to the server
			form$.append("<input type='hidden' name='data[Stripe][token]' value='" + token + "'/>");
			// and submit (not blocked anymore)
			form$.get(0).submit();
		}
	}

</script>


<div class="row">
	<div class="span8 offset2">

		<?php echo $this->Form->create('User', array('id' => 'LoginForm', 'class' => 'well clearfix', 'url' => $this->here)); ?>

			<div class="formLeft">

					<fieldset>
						<legend>Sign Up Details</legend>

						<? echo $this->General->input('User.email',array('label' => 'Email Address')); ?>

						<? echo $this->General->input('User.pswd',array('type' => 'password', 'label' => 'New Password')); ?>
						
					</fieldset>

					<br />

					<fieldset>

						<? if(isset($payment) && $payment){ ?>
							
							<div class="row">
								<div class="span8 offset3">
									Payment has been processed. 
									<? echo $this->Form->input('blank',array('type' => 'hidden')); ?>
								</div>
							</div>

						<? } else { ?>
							
							<!--
							<div class="clearfix">
								<label>Amount</label>
								<div class="input">
									<input type="text" size="20" autocomplete="off" class="charge-amount" disabled value="<? echo $plan['cost']; ?>"/>
								</div>
							</div>
							-->

							<div class="clearfix">
								<label>Card Number</label>
								<div class="input">
									<input type="text" size="20" autocomplete="off" class="card-number" value="<? echo STRIPE_PRESET_CARD_VALUE; ?>"/>
								</div>
							</div>

							<div class="clearfix">
								<label>CVC</label>
								<div class="input">
									<input type="text" size="4" autocomplete="off" class="card-cvc mini" placeholder="3 digits on back of card" maxlength="3"/>
								</div>
							</div>

							<div class="clearfix">
								<label>Expiration (MM/YYYY)</label>
								<div class="input">
									<input type="text" size="2" class="card-expiry-month input-mini" maxlength="2" placeholder="MM"/>
									<span> / </span>
									<input type="text" size="4" class="card-expiry-year input-mini" maxlength="4" placeholder="YYYY"/>
								</div>
							</div>

							<div class="clearfix input payment-errors">
								
							</div>
						
						<? } ?>
						
						<?php echo $this->Form->submit('Sign Up', array('class' => 'btn btn-success', 
																				'div' => array('class' => 'actions'),
																				'after2' => ' or '.$this->Html->link('Request Invite','/invites/add'))); ?>
					
					</fieldset>

			</div>
			<div class="formRight">

				<fieldset>
					<legend><? echo $plan['name']; ?> Plan Details</legend>

					<br />
					
					<h3><? echo $plan['cost']; ?></h3>

					<br />
					<? echo $plan['numbers']; ?>
					<br />
					<? echo $plan['messages']; ?>

				</fieldset>

				<br />
				<br />
				<br />
				<br />

				<div class="alert alert-block alert-info">
					We do not store your credit card information
				</div>

			</div>
			
			<?php echo $this->Form->end(); ?>
	</div>
</div>