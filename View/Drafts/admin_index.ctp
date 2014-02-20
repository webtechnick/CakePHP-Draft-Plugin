<div class="drafts index p20">
	<h2><?php echo __('Drafts'); ?></h2>
	<?php echo $this->element('Draft.admin_filter', array('model' => 'Draft')); ?>
	<?php echo $this->element('Draft.pagination'); ?>
	<table class="table table-striped table-bordered mt20" cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('model'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($drafts as $draft): ?>
	<tr>
		<td><?php echo h($draft['Draft']['id']); ?>&nbsp;</td>
		<td><?php echo h($draft['Draft']['model_id']); ?>&nbsp;</td>
		<td><?php echo h($draft['Draft']['model']); ?>&nbsp;</td>
		<td><?php echo h($draft['Draft']['user_id']); ?>&nbsp;</td>
		<td><?php echo h($draft['Draft']['created']); ?>&nbsp;</td>
		<td><?php echo h($draft['Draft']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<div class="btn-group btn-group-vertical">
				<?php echo $this->Html->link('<i class="icon-eye-open"></i> View', array('action' => 'view', $draft['Draft']['id']), array('class' => 'btn btn-mini', 'escape' => false)); ?>
				<?php echo $this->Html->link('<i class="icon-white icon-trash"></i> Discard', array('admin' => false, 'action' => 'delete', $draft['Draft']['model'], $draft['Draft']['model_id']), array('class' => 'btn btn-mini btn-danger', 'escape' => false), __('Are you sure you want to delete # %s?', $draft['Draft']['id'])); ?>
			</div>
		</td>
	</tr>
	<?php endforeach; ?>
	</table>
	<?php echo $this->element('Draft.pagination'); ?>
</div>
