

<h2>Plans and Pricing</h2>

<br />
<br />

<div class="row">

	<? foreach($plans as $plan){ ?>

		<div class="span3 plan_holder">
			
			<h2><? echo $plan['name']; ?></h2>
			<h3><? echo $plan['cost']; ?></h3>

			<br />
			<? echo $plan['numbers']; ?>
			<br />
			<? echo $plan['messages']; ?>*
			<br />
			<br />
			<? echo $this->Html->link('Sign Up','/users/purchase/'.$plan['key'],array('class' => 'btn btn-success')); ?>

		</div>

	<? } ?>

</div>

<p>
	<br />
	* SMS Messages include both sent and received
</p>



<? return; ?>



<div class="row">
	<div class="span6 offset3" style="width:500px;">

		<? foreach($plans as $plan){ ?>

			<div class="row">
				<div class="span2">
					&nbsp;
				</div>
				<div class="span2">
					<h3><? echo $plan['name']; ?></h3>
					<? echo $plan['numbers']; ?>
					<br />
					<? echo $plan['messages']; ?>
				</div>
				<div class="span2 text-center">

					<? echo $plan['cost']; ?>

					<br />
					<? echo $this->Html->link(ucfirst($buy_button).' '.$plan['name'],'/users/purchase/'.$plan['key'],array('class' => 'btn btn-primary')); ?>

				</div>
			</div>

		<? } ?>

	</div>
</div>