<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		// Favicon
		echo $this->Html->meta('icon');

		// Javascript
		echo $this->Html->script(array('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js',
									   'bootstrap-twipsy.js',
									   'code.js'));

		// CSS
		echo $this->Html->css('bootstrap');

	?>
</head>
<body>
	<div class="container">
		
		<div id="content">

			<?php echo $content_for_layout; ?>

		</div>
		
	</div>
</body>
</html>