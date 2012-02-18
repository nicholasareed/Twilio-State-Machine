
<h1>Rounds Status</h1>

<? echo $this->Html->link('Add a Round','/rounds/add',array('class' => 'btn default bump-left')); ?>


	<table class="zebra-striped sortable">
		
		<thead>
			<tr>
				<th>
					<!-- Edit button -->
				</th>

				<th>
					Name
				</th>
				<th>
					Venue
				</th>
				<th>
					Hacks
				</th>
				<th>
					Judges
				</th>
				<th>
					Venue
				</th>
				<th>
					Level
				</th>
				<th>
					Accept Submission
				</th>
				<th>
					Judging
				</th>
			</tr>
		</thead>

		<tbody>
			<? foreach($rounds as $round): ?>
				<tr>
					<td>
						<? echo $this->Html->link('View Round','/rounds/view/'.$round['Round']['id'],array('class' => 'btn default')); ?>
					</td>
					<td>
						<? echo $round['Round']['name']; ?>
					</td>
					<td>
						<? echo $round['Venue']['name']; ?>
					</td>
					<td>
						<? echo count($round['Hack']); ?> Hacks
					</td>
					<td>
						<? echo count($round['Judge']); ?> Judges
					</td>
					<td>
						<? echo $round['Venue']['name']; ?>
					</td>
					<td>
						<? echo $this->Html->link($round['Round']['level'],'/rounds/level/'.$round['Round']['level']); ?>
					</td>
					<td>
						<?
							echo $round['Round']['can_submit_to'] ? 'Yes' : '';
						?>
					</td>
					<td>
						<?
							echo $round['Round']['judging_status'];
						?>
					</td>
				</tr>
			<? endforeach; ?>
		</tbody>

	</table>

		