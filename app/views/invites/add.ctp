
<div class="row">
	<div class="span6 offset3">
		
		<form class="well form-inline" style="text-align:center;" action="/invites/add" method="POST">
			
			<div class="control-group error">
				<span class="help-inline"><? echo isset($vErrors['email']) ? $vErrors['email'] : '' ?></span>
			</div>
			

			<input type="text" name="data[Invite][email]" class="" placeholder="Email Address" value="<? echo isset($this->data['Invite']['email']) ? $this->data['Invite']['email'] : ''; ?>">
			<button type="submit" class="btn btn-success">Request Invite</button>

		</form>

	</div>
</div>