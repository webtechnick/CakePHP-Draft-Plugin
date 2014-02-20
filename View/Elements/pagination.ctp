<?php
$modulus = isset($modulus) ? $modulus : 6;
$show = isset($show) ? $show : true;
$force_show = isset($force_show) ? $force_show : true;
$options = isset($options) ? $options : array('escape' => false);
if(isset($filter)){
	$options = array_merge(array('url' => array($filter)), $options);
}
$this->Paginator->options($options);
?>
<?php if ($show && ($this->Paginator->hasPrev() || $this->Paginator->hasNext() || $force_show)): ?>
	<div class="pagination mt0">
		<ul>
			<?php if (!$is_mobile && $this->Paginator->hasPrev()): ?>
				<li><?php echo str_replace('/page:1"','"',$this->Paginator->first('&laquo; First', array('escape' => false))); ?></li>
				<li><?php echo str_replace('/page:1"','"',$this->Paginator->prev('&laquo; Previous', array('escape' => false))); ?></li>
			<?php endif; ?>
			<?php echo str_replace('/page:1"','"',$this->Paginator->numbers(array(
				'tag' => 'li', 
				'separator' => ' ', 
				'modulus' => $modulus,
			)));?>
			<?php if (!$is_mobile && $this->Paginator->hasNext()): ?>
				<li><?php echo $this->Paginator->next('Next &raquo;', array('escape' => false)); ?></li>
				<li><?php echo $this->Paginator->last('Last &raquo;', array('escape' => false)); ?></li>
			<?php endif; ?>
		</ul>
	</div>
	<span class="label"><?php echo $this->Paginator->counter('Page {:page} of {:pages} - {:count} Results'); ?></span>
<?php endif; ?>
<div class="clear"></div>