


<table class="table table-bordered table-striped table-condensed table-sortable">
	<thead>
		<tr>
			<th>Email</th>
			<th>Plan</th>
			<th>PTNs</th>
			<th>Apps</th>
		</tr>
	</thead>
	<tbody>

		<? foreach($users as $user){ ?>

			<tr>
				<td>
					<? echo $user['User']['email']; ?>
				</td>
				<td>
					<? echo $user['User']['plan']; ?>
				</td>
				<td>
					<? echo count($user['Twilio']); ?>
				</td>
				<td>
					<? echo count($user['Project']); ?>
				</td>
			</tr>

		<? } ?>

	</tbody>
</table>