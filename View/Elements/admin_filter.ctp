<?php echo $this->Html->script('/draft/js/clear_default'); ?>
<?php $model = isset($model) ? $model : false; ?>
<div id="admin_filter" class="pull-right">
	<?php	if ($model) : ?> 
		<?php echo $this->Form->create($model, array('inputDefaults' => array('label' => false,'div' => false))); ?>
		<div class="input-append">
			<?php echo $this->Form->input('filter', array('type' => 'text', 'class' => 'span3 clear_default', 'value' => $model.' Search')).'<button type="submit" class="btn btn-primary"><i class="icon-search icon-white"></i> Search</button>'; ?>
		</div>
		</form>
	<?php endif; ?>
</div>