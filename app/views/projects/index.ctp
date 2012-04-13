
<!-- Advice/Hint -->
<div class="row no-more-left">

<!-- Apps -->
	<div class="span6 appsRow row-striped">
		
		<div class="row appRow noleft">
			<div class="span2">
				<h3>My Apps</h3>
			</div>
			<div class="span2 offset1 text-center">
				<a href="/projects/add" class="btn btn-success">Create New App</a>
			</div>
		</div>


		<? foreach($projects as $project): ?>
			
			
			<div class="row appRow noleft">
				<div class="span2">
					<h4><? echo $this->Html->link($project['Project']['name'],'/projects/view/'.$project['Project']['id']); ?></h4>
				</div>
				<div class="span2">
					<? 
						$count = count($project['Twilio']);
						if($count == 1){
							echo $this->General->prettyPhone($project['Twilio'][0]['ptn']);
						} elseif($count == 0) {
							echo '0 numbers';
						} else {
							echo $count.' numbers';
						}
					?>
				</div>
				<div class="span1">
					<? echo $project['Project']['pp_count']; ?>
					<? echo $project['Project']['pp_count'] == 1 ? 'user' : 'users'; ?>
				</div>
			</div>
				
			
		<? endforeach; ?>

		<? if(empty($projects)){ ?>

			<div class="row appRow noleft">
				<div class="span5 text-center">
					You should create an App!
				</div>
			</div>

		<? } ?>

	</div>

	<div class="span6 force-no-left">

		<div class="row">
			<div class="span6">

				<!--
				<? if($extra_numbers > 0){ ?>
					<div class="alert alert-block alert-danger">
						<strong>Extra Numbers</strong> 
						<br />
						You can register  <strong><? echo $extra_numbers; ?></strong> more number(s)
						<br />
						<? echo $this->Html->link('Choose Numbers','/numbers',array('class' => 'btn btn-default btn-mini')); ?>
					</div>
				<? } ?>
				-->
				<!--
				<div class="alert alert-block alert-info">
					<strong>Getting Started</strong>
					<br />
					Create your first Application using the <strong>Create New App</strong> button below. Each Application you create will have a phone number that people will send SMS messages to.
				</div>
			-->
			</div>
		
		</div>

	</div>

</div>

<? return; ?>
