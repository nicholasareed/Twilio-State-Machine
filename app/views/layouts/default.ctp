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
									   'tablesorter.js',
									   'boostrap-tabs.js',
									   'bootstrap-twipsy.js',
									   'jquery.scrollTo.js',
									   'jquery.tmpl.min.js',
									   'Keyboard.js',
									   'code_keyboard.js',
									   'code.js'));
		if(isset($this->additionalScripts)){
			foreach($this->additionalScripts as $script){
				echo $this->Html->script($script);
			}
		}

		// CSS
		echo $this->Html->css('bootstrap');

	?>
</head>
<body>

	<? echo $this->element('header'); ?>

	<div class="container">
		
		
		<div id="content">

			<?php echo $this->Session->flash(); ?>

			<?php echo $content_for_layout; ?>

		</div>
		
	</div>

	<? echo $this->element('footer'); ?>

	<!-- Google Analytics -->


	<?php echo $this->element('sql_dump'); ?>
</body>
</html>