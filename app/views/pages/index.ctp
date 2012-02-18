

<h1>Pages</h1>

<? echo $html->link('New Page','/pages/add'); ?>

<table class="zebra-striped sortable">
	
	<thead>
		<tr>
			<th>
				
			</th>
			<th>
				Key
			</th>
			<th>
				Type
			</th>
			<th>
				Title
			</th>
			<th>
				Internal Note
			</th>
			<th>
				Last Modified
			</th>
			<th>
				
			</th>
			<th>
				
			</th>
		</tr>
	</thead>

	<tbody>
		<? foreach($pages as $page): ?>
			<tr>
				<td>
					<?	
						if($page['Page']['live']){
							echo '<span class="label success">Active</span>';
						} else {
							echo '<span class="label important">Inactive</span>';
						}
					?>
				</td>
				<td>
					<?= $page['Page']['key']; ?>
				</td>
				<td>
					<?= $page['Page']['type']; ?>
				</td>
				<td>
					<?= $page['Page']['title']; ?>
				</td>
				<td>
					<?= $page['Page']['note']; ?>
				</td>
				<td>
					<?	
						// Todo
						echo "time ago here";
					?>
				</td>
				<td>
					<?= $this->Html->link('View','/pages/'.$page['Page']['key']); ?>
				</td>
				<td>
					<?= $this->Html->link('Customize','/pages/edit/'.$page['Page']['id']); ?>
				</td>
			</tr>
		<? endforeach; ?>
	</tbody>

</table>
