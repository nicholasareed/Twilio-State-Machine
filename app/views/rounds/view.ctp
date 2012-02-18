
<script type="text/javascript">
	$(document).ready(function(){

		// On change, move to a new Round
		$('.moveHacks').on('click',function(){
			var round_id = $('#RoundAction').val();

			if(!round_id){
				// Return if no Round selected
				alert('No Round selected');
				return false;
			}

			var checkboxes = [];
			$('input[type="checkbox"]:checked').each(function(){
				checkboxes[checkboxes.length] = $(this).val();
			});

			// No Hack selected?
			if(!checkboxes.length){
				alert('No Hacks selected');
				return false;
			}

			// Info window
			$('.infoWindow').removeClass('nodisplay');

			// POST
			$.ajax({
				url: '/hacks/move_to_round/'+round_id,
				type: 'POST',
				data: {'data[Hack][ids][]':checkboxes},
				success: function(response){
					// Remove waiting div
					$('.infoWindow').addClass('nodisplay');

					// Reload on response (error/success in Flash message)
					window.location.href=window.location.href;
				}
			});

			return false;

		});

	});
</script>

<div class="alert-message block-message">
	Perform actions on many Hacks
</div>

<h2><? echo $round['Round']['name']; ?> <small><? echo $round['Venue']['name']; ?></small></h2>
	
<div class="row">
	<div class="span2 text-right">
		<h3 class="off-color">Level</h3>
	</div>
	<div class="span6">
		<h3><? echo $round['Round']['level']; ?></h3>
	</div>
</div>
<div class="row">
	<div class="span2 text-right">
		<h3 class="off-color">Submissions</h3>
	</div>
	<div class="span6">
		<h3><? echo $round['Round']['entries_status']; ?></h3>
	</div>
</div>
<div class="row">
	<div class="span2 text-right">
		<h3 class="off-color">Judging</h3>
	</div>
	<div class="span6">
		<h3><? echo $round['Round']['judging_status']; ?></h3>
	</div>
</div>


<h2>Hacks</h2>
<? if($_EAuth['Access']['admin']){ ?>
	<? echo $this->Html->link('Edit Round','/rounds/edit/'.$round['Round']['id'],array('class' => 'btn default bump-left')); ?>

	<p>
		<? echo $this->Form->input('Round.action',array('type' => 'select','label' => false, 'empty' => true, 'options' => $actions, 'after' => $this->Html->link('Move Hacks','/',array('class' => 'moveHacks btn small default bump-left')))); ?>

		<div class="alert-message block-message infoWindow error nodisplay">
			Submitting...Please wait
		</div>

	</p>

<? } ?>

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
			<th>
				<!-- Remove -->
			</th>
		</tr>
	</thead>

	<tbody>
		<? foreach($round['Hack'] as $hack): ?>
			<tr>
				<td>
					<?
						echo $this->Form->input('Hack'.'.hack_'.$hack['id'],array('type' => 'checkbox', 'value' => $hack['id'], 'label' => false, 'div' => false));
					?>
				</td>
				<td>
					<?
						// Average of the Reviews
						// - probably should do this in the Model, instead of going to Spaghetti Land
						// - toss out highest and lowest? 
						$ratings = Set::extract($hack['Review'],'{n}.rating');
						$sum = array_sum($ratings);
						$avg = count($ratings) ? round($sum / count($ratings),10) : 0;
						echo number_format($avg,2);
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
				<td>
					<? echo $this->Html->link('Remove Hack from Round','/hacks/remove_from_round/'.$hack['HacksRound']['id'],array('class' => 'btn small error')); ?>
				</td>
			</tr>
		<? endforeach; ?>
	</tbody>

</table>


<h2>Judges</h2>

<p>
	<? echo $this->Html->link('Add Judges','/rounds/add_judges/'.$round['Round']['id'],array('class' => 'btn default bump-left')); ?>
</p>


<table class="zebra-striped sortable">
	
	<thead>
		<tr>
			<th>
				Name
			</th>
			<th>
				Number of Reviews
			</th>
		</tr>
	</thead>

	<tbody>
		<? foreach($round['Judge'] as $judge): ?>
			<tr>
				<td>
					<? echo $this->Html->link($judge['User']['username'],'/attendees/view/'.$judge['User']['username']); ?>
				</td>
				<td>
					<? echo count($judge['Review']); ?>
				</td>
			</tr>
		<? endforeach; ?>
	</tbody>

</table>

