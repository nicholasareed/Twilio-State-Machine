
<!-- Advice/Hint -->
<div class="row">
	<div class="span6 offset3">

		<div class="row">
			<div class="alert alert-block alert-info">
				<strong>Getting Started:</strong> create your first Application using the "Create New App" button below. Each Application you create will have a phone number that people will send SMS messages to.
			</div>
		</div>

	</div>
</div>

<!-- Apps -->
<div class="row">
	<div class="span6 offset3 appsRow row-striped">
		
		<div class="row appRow">
			<div class="span2">
				<h3>My Apps</h3>
			</div>
			<div class="span2 offset2 text-center">
				<a href="/projects/add" class="btn btn-success">Create New App</a>
			</div>
		</div>


		<? foreach($numbers as $number): ?>
			
			
			<div class="row appRow">
				<div class="span2">
					<h4><? echo $this->General->prettyPhone($number['Number']['ptn']); ?></h4>
				</div>
				<div class="span3 text-center">
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
</div>

<? return; ?>
