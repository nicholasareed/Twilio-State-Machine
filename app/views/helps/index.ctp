
<h2>Help Index</h2>
<hr>
<? echo $this->Html->link('New Help Topic','/helps/add',array('class' => 'btn btn-default')); ?>
<br />
<br />

<? foreach($helps as $help){ ?>
		
	<? echo $this->Html->link($help['Help']['key'],'/helps/edit/'.$help['Help']['id']); ?>
	<br />
	<br />


<? } ?>