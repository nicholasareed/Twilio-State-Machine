
<h4>Application Database</h4>
<!-- a.path.is.here -->
<pre>
	<? echo jsonIndent($project['Project']['meta']); ?>
</pre>


<h4>Users</h4>
<!-- u.path.is.here -->
<? 
	foreach($pp as $key => $p){

		$p['PhonesProject']['meta'] = json_decode($p['PhonesProject']['meta']);
		unset($p['PhonesProject']['project_id']);
		unset($p['PhonesProject']['phone_id']);
		$pp[$key] = $p;
		continue;
	}

	// Extract out the extra shit
	$tmp = Set::extract($pp,'{n}.PhonesProject');

	echo "<pre>";
	echo jsonIndent(json_encode($tmp));
	echo "</pre>";
?>