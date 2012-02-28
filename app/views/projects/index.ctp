
<h2>Applications</h2>

<table class="zebra-striped sortable">
	
	<thead>
		<tr>
			<th>
				Name
			</th>
			<th>
				Number of Users
			</th>
		</tr>
	</thead>

	<tbody>
		<? foreach($projects as $project): ?>
			<tr>
				<td>
					<? echo $this->Html->link($project['Project']['name'],'/projects/view/'.$project['Project']['id']); ?>
				</td>
				<td>
					
				</td>
			</tr>
		<? endforeach; ?>
	</tbody>

</table>

