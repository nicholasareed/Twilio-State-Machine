
<?

	App::import('Vendor', 'Markdown', array('file' => 'Markdown/markdown.php'));
	$Help =& ClassRegistry::init('Help');
	$conditions = array('Help.live' => 1);
	$helps = $Help->find('all',compact('conditions')); // should be cached
	foreach($helps as $help){
?>
		<script id="t_help_<? echo $help['Help']['key']; ?>" type="text/x-jquery-tmpl">

			<? echo Markdown($help['Help']['markdown']); ?>

		</script>

<? } ?>

<?
return; 
?>



<script id="t_help_webhook" type="text/x-jquery-tmpl">

	<h3>Webhook</h3>

</script>

<script id="t_help_response" type="text/x-jquery-tmpl">

	<h3>Response</h3>

</script>

<script id="t_help_attribute" type="text/x-jquery-tmpl">

	<h3>Set Attribute</h3>

	<p>
		Attributes are used to store information about your users and your application. 

		For example, when a person sends a command such as "name" I could then ask them what their full name was. 
		When they respond, I can set an Attribute for the user. 

		u.meta.name={w}

		The "u.meta.name" part is where we want to store the value. 
		The "{w}" part is where we get the words they texted in. 

		There are two types of Attributes:
		- Application
		- User

		Each User has two values that you cannot edit: "u.ptn", and "u.id"
		But, you can set any values for the current user/caller like:

		"u.meta.thing_to_set=value_here"

	</p>

</script>

<script id="t_help_state" type="text/x-jquery-tmpl">

	<h3>Set State</h3>

	<p>
		A "state" is a...
	</p>

</script>

