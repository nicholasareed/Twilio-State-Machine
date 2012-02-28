<!-- Load additional JS -->
<? $this->additionalScripts = array('code_application.js'); ?>

<!-- Project ID -->
<div id="Project" project_id="<? echo $project['Project']['id']; ?>" class="nodisplay"></div>


<!-- Templates -->
<script id="t_span_editable" type="text/x-jquery-tmpl">

	${type}: <span class="editable">${value}</span>

</script>


<script id="t_conditionRow" type="text/x-jquery-tmpl">

	<div db_id="${id}" class="row conditionRow" condition_id="${id}" data-level="3" depth-search=".actionsRow">
		<div class="span13">
			<span class="left_roundy">
				(
			</span>
			<span class="label">
				${type}: <span class="editable">${input1}</span>
			</span>


			<span class="edit_inline nodisplay" data-url="/conditions/edit/${id}/${hash}" data-remove-url="/conditions/remove/${id}/${hash}">
				<input type="text" value="${input1}" />
			</span>


			<span class="right_remove">
				<a href='#'>remove</a>
			</span>

		</div>

	</div>

</script>


<script id="t_actionRow" type="text/x-jquery-tmpl">

	<div db_id="${id}" class="row actionRow" action_id="${id}" data-level="4">
		<div class="span6">

				<span class="left_roundy">
					{
				</span>
					
				then

				<span class="label">
					${type}: <span class="editable">${input1}</span>
				</span>

				<span class="edit_inline nodisplay" data-url="/actions/edit/${id}/${hash}" data-remove-url="/actions/remove/${id}/${hash}">
					<input type="text" value="${input1}" />
				</span>

				<span class="right_remove">
					<a href='#'>remove</a>
				</span>



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
		<div class="span1 text-right" style="text-align:right;">
			<!--
			<h3>
				<span class="cancollapse collapsed" collapse-level="4">
					&nbsp;+&nbsp;
				</span>
			</h3>
			-->
			<span class="else">else </span> <span class="if">if</span>

		</div>
		<div class="span6 actualStep">
			
			<!-- Conditions -->
			<div class="conditionsRow collapsed">
				{{each Condition}}
					{{tmpl($value) "#t_conditionRow"}}
				{{/each}}
				
				<!-- Add a Condition -->
				<div class="row conditionRow addConditionRow addAble transparentNoHover" depth-search=".actionsRow" data-level="3" >
					<div class="span4">
						<span class="left_roundy">
							(
						</span>
						<a step="${id}" class="addCondition" href="/conditions/add/${id}">Add Condition</a
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
					<div class="span6">
						<span class="left_roundy">
							{
						</span>
							<a class="addAction" step="${id}" href="/actions/add/${id}">Add Action</a>
						<span class="right_roundy">
							}
						</span>
					</div>
				</div>
			
			</div>
			
		</div>
	</div>
	
</script>

<script id="t_stateRow" type="text/x-jquery-tmpl">
	<div class="stateRow" data-level="1" db_id="${id}">
					
		<br>
		<h2 db_id="${id}" class="elem_state" style="display:none;">
			<small>State</small>
			<br>
			<span class="cancollapse state_collapse" collapse-level="2">&nbsp;-&nbsp;</span>
			<span>${key}</span>
		</h2>

		<!-- Step -->
		
		<div class="stepRows">
			{{each Step}}
				{{tmpl($value) "#t_step"}}
			{{/each}}
		
			<!-- Add Step -->
			<div class="row stepRow addStepRow" data-level="2">
				<div class="span2 offset1">
					<h5>
						<a href="/steps/add/${id}/${hash}" class="addStep">Add Step Here</a>
					</h5>
				</div>
			</div>
		</div>
	
	</div>
</script>

<script id="t_project" type="text/x-jquery-tmpl">
	
	<h1>
		<small>Application</small>
		<br />
		${Project.name}
	</h1>

	{{each State}}
		{{tmpl($value) "#t_stateRow"}}
	{{/each}}
		
	<!-- Add State -->
	<!--
	<div class="row stateRow addStateRow addAble">
		<div class="span8">
			<h5>
				<a href="/states/add/${Project.id}/${hash}" class="addState">Add State Here</a>
			</h5>
		</div>
	</div>
	-->
</script>


<!-- Application layout -->
<div class="row">
	<div class="span7">

		<div id="project_view">
			Loading Application...
		</div>

	</div>
	<div class="span5">
		

		<div class="form_holder">
			
		</div>

		<div id="test_view" class="collapsed">
			<? echo $this->element('test_sms'); ?>
		</div>


		<div class="tabbable">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#1" data-toggle="tab">Logs</a>
				</li>
				<li>
					<a href="#2" data-toggle="tab">Database</a>
				</li>
				<li>
					<a href="#3" data-toggle="tab">Settings</a>
				</li>
				<li>
					<a href="#4" data-toggle="tab">Help</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="1">
					
					<!-- Logs -->
					<div class="log_holder">
						
						<p>
							<? echo $this->Html->link('Refresh','/',array('class' => 'moreLogs')); ?>
						</p>

						<div id="logs">
							
						</div>

					</div>

				</div>
				<div class="tab-pane" id="2">
					<p>
						<? echo $this->Html->link('Refresh','/',array('class' => 'refreshDatabase')); ?>
					</p>

					<div class="database_holder">
						Loading Database...
					</div>
				</div>
				<div class="tab-pane" id="3">
					<p>
						[chechbox] Enable States (?)
					</p>
				</div>
				<div class="tab-pane" id="4">
					<p>
						Help information will be in here. Syntax, guides, etc.
					</p>
				</div>
			</div>
		</div>	
	</div>
</div>
