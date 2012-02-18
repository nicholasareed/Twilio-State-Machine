
<script type="text/javascript">
	$(document).ready(function(){

		// On change, move to a new Round
		$('#RoundAction').on('change',function(){
			console.log($(this).val());
		});

	});
</script>

<h1>Round Level</h1>

<div class="alert-message block-message">
	Perform actions on many Hacks at once. 
</div>

<p>
	<? echo $this->Form->input('Round.action',array('type' => 'select','label' => 'Move Selected to: ', 'empty' => true, 'options' => $actions)); ?>
</p>

<? foreach($rounds as $round){ ?>
	
	<h2><? echo $round['Round']['name']; ?> <small><? echo $round['Venue']['name']; ?></small></h2>

	<table class="zebra-striped sortable">
		
		<thead>
			<tr>
				<th>
					<!-- Checkbox -->
				</th>
				
				<!-- Display Scores -->
				<th>
					Average Score
				</th>

				<th>
					Title
				</th>
				<th>
					Team
				</th>
				<th>
					Number of Reviews
				</th>
			</tr>
		</thead>

		<tbody>
			<? foreach($round['Hack'] as $hack): ?>
				<tr>
					<td>
						<?
							echo $this->Form->input('Round_'.$round['Round']['id'].'.hack_'.$hack['id'],array('type' => 'checkbox', 'value' => $hack['id'], 'label' => false, 'div' => false));
						?>
					</td>
					<td>
						<?
							// Average of the Reviews
							// - probably should do this in the Model, instead of going to Spaghetti Land
							// - toss out highest and lowest? 
							$ratings = Set::extract($hack['Review'],'{n}.rating');
							$avg = count($ratings) ? round(array_sum($ratings) / count($ratings),1) : 0;
							echo $avg;
						?>
					</td>
					<td>
						<? echo $this->Html->link($hack['title'],'/hacks/view/'.$hack['id']); ?>
					</td>
					<td>
						<? echo $this->Html->link($hack['Team']['name'],'/teams/view/'.$hack['Team']['id']); ?>
					</td>
					<td>
						<? echo count($hack['Review']); ?>
					</td>
				</tr>
			<? endforeach; ?>
		</tbody>

	</table>

<? } ?>