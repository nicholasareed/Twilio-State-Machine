<!-- Load additional JS -->
<? $this->additionalScripts = array('code_application.js'); ?>

<? echo $this->element('help_templates'); ?>

<!-- Project ID -->
<div id="Project" project_id="<? echo $project['Project']['id']; ?>" class="nodisplay"></div>


<!-- Templates -->
<script id="t_span_editable" type="text/x-jquery-tmpl">

	${type}: <span class="editable">${value}</span>

</script>


<script id="t_conditionRow" type="text/x-jquery-tmpl">

	<div db_id="${id}" class="row conditionRow" condition_id="${id}" data-level="3" depth-search=".actionsRow">
		<div class="span11">
			<span class="left_roundy">
				(
			</span>
			<span class="conditionOrAction do-tooltip" title="double-click to edit" db_id="${id}" data-copy-url="/conditions/copy/${id}/${hash}" data-edit-url="/conditions/edit/${id}/${hash}" data-remove-url="/conditions/remove/${id}/${hash}">
				<i class="help-icon icon-question-sign" data-help-trigger="${type}"></i>
				<span class="type_holder">
					${type}:
				</span>
				<span class="editable">${input1}</span>

				<div class="popover" style="display:none;">
					{{if type == "starts_with"}}
						Case-sensitive: 
						{{if case_sensitive == '1'}}
							<span class="label label-success">Yes</span>
						{{else}}
							<span class="label label-important">No</span>
						{{/if}}
					{{else type == "contains"}}	
						Case-sensitive: 
						{{if case_sensitive == '1'}}
							<span class="label label-success">Yes</span>
						{{else}}
							<span class="label label-important">No</span>
						{{/if}}
					{{else type == "attribute"}}	
						Case-sensitive: 
						{{if case_sensitive == '1'}}
							<span class="label label-success">Yes</span>
						{{else}}
							<span class="label label-important">No</span>
						{{/if}}
					{{/if}}
				</div>

			</span>


			<span class="edit_inline nodisplay">
				<input type="text" value="${input1}" />
			</span>

			<span class="right_andand">
				&nbsp;&amp;&amp;
			</span>

			<!--
			<span class="right_remove">
				<a href='#'>remove</a>
			</span>
			-->

		</div>

	</div>

</script>


<script id="t_actionRow" type="text/x-jquery-tmpl">

	<div db_id="${id}" class="row actionRow" action_id="${id}" data-level="4">
		<div class="span1 then">
			then
		</div>
		<div class="span10">

				<span class="left_roundy">
					&nbsp;&nbsp;{
				</span>
				<!--
				then
				-->
				<span class="conditionOrAction do-tooltip" title="double-click to edit" data-edit-url="/actions/edit/${id}/${hash}" data-remove-url="/actions/remove/${id}/${hash}">
					<i class="help-icon icon-question-sign" data-help-trigger="${type}"></i>
					<span class="type_holder">
						${type}:
					</span>
					<span class="editable">${input1}</span>
					<div class="popover" style="display:none;">
						{{if type == "send_sms"}}	
							Recipient(s): ${send_sms_recipients}
							<br />
							Send Delay: ${send_sms_later_time_text}
						{{else type == "webhook"}}	
							{{if webhook_can_modify_vars == '1'}}
								<span class="label label-success">CAN</span> modify variables
							{{else}}
								<span class="label label-important">CAN NOT</span> modify variables
							{{/if}}
						{{/if}}
					</div>
				</span>

				<span class="edit_inline nodisplay">
					<input type="text" value="${input1}" />
				</span>

				<!--
				<span class="right_remove">
					<a href='#'>remove</a>
				</span>
				-->


			</h4>
		</div>
	</div>

</script>


<script id="t_addCondition" type="text/x-jquery-tmpl">

	<form accept-charset="utf-8" method="post" action="/conditions/add/">
		<div style="display:none;">
			<input type="hidden" value="POST" name="_method">
		</div>	

		<div class="clearfix select">
			<div class="input">
				<select id="ConditionType" help="" name="data[Condition][type]">
				<option value=""></option>
				<option value="starts_with">Starts with...</option>
				<option value="regex">Regular Expression Match</option>
				<option value="word_count">Word Count</option>
				<option value="attribute">User Attribute</option>
				<option value="default">Default</option>
				</select>
			</div>
		</div>
		<div class="clearfix hidden">
			<label for="HiddenStep">Step</label>
			<div class="input">
				<input type="hidden" id="HiddenStep" value="submitted_type" help="" name="data[Hidden][step]">
			</div>
		</div>
		<div class="actions">
			<input type="submit" value="Next" class="btn primary">
		</div>

	</form>

</script>


<script id="t_step3" type="text/x-jquery-tmpl">
	
	${Condition[0].id}

</script>

<script id="t_step" type="text/x-jquery-tmpl">

	<div db_id="${id}" class="row stepRow" data-level="2">
		<div class="span1 text-right elseif" style="text-align:right;" data-remove-url="/steps/remove/${id}/${hash}">
			<!--
			<h3>
				<span class="cancollapse" collapse-level="4">
					&nbsp;+&nbsp;
				</span>
			</h3>
			-->
			<span class="else">else </span> <span class="if">if</span>

		</div>
		<div class="span11 actualStep">
			
			<!-- Conditions -->
			<div class="conditionsRow">
				{{each Condition}}
					{{tmpl($value) "#t_conditionRow"}}
				{{/each}}
				
				<!-- Add a Condition -->
				<div class="row conditionRow addConditionRow addAble transparentNoHover" depth-search=".actionsRow" data-level="3" >
					<div class="span4">
						<span class="left_roundy">
							(
						</span>
						<a step="${id}" class="addCondition" href="/conditions/add/${id}" original="+matches this">+matches this</a>
						<span class="right_roundy">
							)
						</span>
					</div>
				</div>
			</div>


			<!-- All Actions -->
			<div class="actionsRow">

				<!-- Action (indented) -->
				{{each Action}}
					{{tmpl($value) "#t_actionRow"}}
				{{/each}}

				<!-- Add an Action -->
				<div class="row actionRow addActionRow addAble transparentNoHover" data-level="4">
					<div class="span1 then">
						then
					</div>
					<div class="span10">
						<span class="left_roundy">
							&nbsp;&nbsp;{
						</span>
							<a class="addAction" step="${id}" href="/actions/add/${id}" original="+do this">
								+do this
							</a>
						<span class="right_roundy">
							&nbsp;}
						</span>
					</div>
				</div>
			
			</div>
			
		</div>
	</div>
	
</script>

<script id="t_stateRow" type="text/x-jquery-tmpl">
	<div class="stateRow" data-level="1" db_id="${id}">

		<h2 db_id="${id}" class="elem_state">
			<!--<span class="cancollapse state_collapse" collapse-level="2">&nbsp;-&nbsp;</span>-->
			<span>${key}</span>
		</h2>

		<!-- Step -->
		
		<div class="stepRows">
			{{each Step}}
				{{tmpl($value) "#t_step"}}
			{{/each}}
		
			<!-- Add Step -->
			<div class="row stepRow addStepRow addAble" data-level="2">
				<div class="span1 text-right">
					
						<a href="/steps/add/${id}/${hash}" class="addStep">
							+<span class="else">else </span> <span class="if">if</span>
						</a>
					
				</div>
			</div>
		</div>
	
	</div>
</script>

<script id="t_project" type="text/x-jquery-tmpl">
	
	<h1>
		<!--
		<small>Application</small>
		<br />
		-->
		${Project.name}
	</h1>

	
	{{if Numbers}}
		<div class="alert alert-info alert-inline-block">
			Test by sending an SMS to: <strong>${Numbers}</strong>
		</div>
	{{else}}
		<div class="alert alert-info alert-inline-block">
			No phone numbers assigned to app
		</div>
	{{/if}}


	<div class="statesRow" state-status="${Project.enable_state}">
		{{each State}}
			{{tmpl($value) "#t_stateRow"}}
		{{/each}}
			
		<!-- Add State -->
		<div class="row stateRow addStateRow addAble">
			<div class="span8">
				<a href="/states/add/${Project.id}/${hash}" class="addState">+state</a>
			</div>
		</div>
	</div>
</script>


<!-- Application layout -->
<div class="main_row row">
	<div class="span12">
		
		<? if(empty($twilios)){ ?>

			<div class="alert alert-block alert-error">
				<h4 class="alert-heading">Missing Phone Number</h4>
				<p>
					It looks like you have not tied any Twilio numbers to this App. 
				</p>
				<? echo $this->Html->link('Manage Phone Numbers','/projects/ptns/'.$project['Project']['id'],array('class' => 'btn btn-small')); ?>
			</div>

		<? } ?>

		<div id="project_view">
			Loading Application...
		</div>

	</div>
	<div class="advanced_tab">
		

		<div class="form_holder">
			
		</div>

		<div class="optionbuttons_holder">
			<? echo $this->Html->link('Inspector','/',array('class' => 'optionButton testTab')); ?>
			<br /><br />	
			<? echo $this->Html->link('Advanced','/',array('class' => 'optionButton advTab')); ?>
		</div>

		<div id="test_view" class="advanced_tab_div collapsed">
			<? echo $this->element('test_sms'); ?>
		</div>


		<div id="advancedOptions" class="advanced_tab_div collapsed tabbable">
			<ul class="nav nav-tabs">
				<!--
				<li class="active">
					<a href="#1" data-toggle="tab">Inspector</a>
				</li>
			-->
				<li class="active">
					<a href="#2" data-toggle="tab">Database</a>
				</li>
				<li>
					<a href="#3" data-toggle="tab">Settings</a>
				</li>
				<li>
					<a href="#4" data-toggle="tab" class="help_panel_tab">Help</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane" id="1">
					
					<!-- Logs -->
					<div class="log_holder">
						
						<p>
							<? echo $this->Html->link('Refresh','/',array('class' => 'moreLogs')); ?>
						</p>

						<div id="logs">
							
						</div>

					</div>

				</div>
				<div class="tab-pane active" id="2">
					<p>
						<? echo $this->Html->link('Refresh','/',array('class' => 'refreshDatabase')); ?>
					</p>

					<div class="database_holder">
						
					</div>
				</div>
				<div class="tab-pane" id="3">
					
					<!-- Project Settings -->
					<?php echo $this->Form->create('Text', array('id' => 'ProjectSetting', 'url' => '/projects/settings/'.$project['Project']['id'])); ?>
						<fieldset>
							
							<? echo $this->General->input('Project.enable_state',array('label' => 'Enable State', 'type' => 'checkbox')); ?>
								
							<?php echo $this->Form->submit('Save Settings', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions'), 'after' => ' <span></span>')); ?>
						
						</fieldset>

					<?php echo $this->Form->end(); ?>
					
					
				</div>
				<div class="tab-pane help_panel" id="4">
					<div id="help_message">
						Help is here
					</div>
				</div>
			</div>
		</div>	
	</div>
</div>
