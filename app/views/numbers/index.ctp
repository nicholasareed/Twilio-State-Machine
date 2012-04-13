
<!-- Advice/Hint -->
<div class="row">

<!-- Apps -->
	<div class="span6 offset0 appsRow row-striped">
		
		<div class="row appRow">
			<div class="span3">
				<h3>My Numbers</h3>
			</div>
			<div class="span2">
				<h3>App</h3>
			</div>
		</div>


		<? foreach($numbers as $number): ?>
			
			
			<div class="row appRow">
				<div class="span3">
					<h4><? echo $this->General->prettyPhone($number['Twilio']['ptn']); ?></h4>
				</div>
				<div class="span3">
					<?
						if(empty($number['Project']['id'])){
							echo "No App";
						} else {
							echo $this->Html->link($number['Project']['name'],'/projects/view/'.$number['Project']['id'],array('class' => 'btn btn-mini btn-default'));
						}
					?>
				</div>
			</div>
				
			
		<? endforeach; ?>

	</div>

	<!-- Sidebar -->
	<div class="span6 offset0">

		<div class="row">
			<div class="span5">
				<div class="alert alert-block alert-info">
					<strong>About Numbers</strong> 
					<br />
					Each Phone Number should be tied to an App. When someone sends a text messge to the number, it will trigger the corresponding App. 
				</div>
			</div>
		</div>


		<div class="row">
			<div class="span5">
				<div class="alert alert-block alert-success clearfix">
					<h3>
						<? echo $extra_numbers; ?> number(s) available
						<? //echo $this->Html->link('Buy a Number','/numbers/buy',array('class' => 'btn btn-success', 'style' => 'float:right;')); ?>
					</h3>
				</div>
			</div>
		</div>

	</div>
</div>

<? return; ?>
