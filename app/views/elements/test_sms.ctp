
<div class="row testSmsRow">
	<div class="span6">

		<?php echo $this->Form->create('Text', array('id' => 'TextTest', 'url' => '/texts/project/'.$project['Project']['id'])); ?>
			<fieldset>
				<!--
				<div class="alert alert-block">
					DEMO_MODE: No actual SMS messages will be sent, just watch the Inspector to see what WOULD have happened
				</div>
				-->

				<div class="row">
					<div class="span3">
						<? echo $this->General->input('Text.to',array('label' => 'To', 'type' => 'select', 'options' => $twilios)); ?>
					</div>
					<div class="span3">
						<? echo $this->General->input('Text.from',array('label' => 'From', 'default' => '+16027059885')); ?>
					</div>
				</div>

				<div class="row">
					<div class="span3">
						<? echo $this->General->input('Text.body',array('label' => 'Body')); ?>
					</div>
					<div class="span3">
						<label>&nbsp;</label>
						<?php echo $this->Form->submit('Test SMS', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions'))); ?>
					</div>
				</div>	
				
			
			</fieldset>

		<?php echo $this->Form->end(); ?>


		<div id="results2" class="nodisplay">

		</div>

	</div>

</div>

<div class="row">
	<div class="span6">

		<div>
			<h3>Inspector <small>[see messages received, actions taken]</small></h3>
		</div>

		<div class="log_holder">
			
			<p>
				<? echo $this->Html->link('Refresh','/',array('class' => 'moreLogs')); ?>
			</p>

			<div id="logs">
				
			</div>

		</div>

	</div>
</div>