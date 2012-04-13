

<? foreach($helps as $help){ ?>
		
	<? echo $this->Html->link($help['Help']['key'],'/helps/edit/'.$help['Help']['id']); ?>
	<br />
	<br />


<? } ?>