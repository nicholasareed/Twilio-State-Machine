
<? echo $this->Html->link('Help Index','/helps'); ?>

<br />
<br />

<!-- Form -->
<?php echo $this->Form->create('Help', array('url' => $this->here)); ?>
	<fieldset>
		<legend><? echo $help['Help']['key']; ?></legend>

		<? echo $this->General->input('Help.markdown',array('label' => false,'style' => 'width:800px;height:400px;')); ?>

		<?php echo $this->Form->submit('Save', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>

<br />
<br />
<?
	App::import('Vendor', 'Markdown', array('file' => 'Markdown/markdown.php'));
	echo Markdown($help['Help']['markdown']);
?>
<br />

<hr>

<? echo $this->Html->link('http://daringfireball.net/projects/markdown/syntax'); ?>