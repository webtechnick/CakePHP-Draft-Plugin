<div class="drafts view p20">
	<h2><?php  echo __('Draft'); ?></h2>
	<table class="table table-striped table-bordered">
		<tr><th><?php echo __('Id'); ?></th>
		<td>
			<?php echo h($draft['Draft']['id']); ?>
			&nbsp;
		</td></tr>
		<tr><th><?php echo __('Model Id'); ?></th>
		<td>
			<?php echo h($draft['Draft']['model_id']); ?>
			&nbsp;
		</td></tr>
		<tr><th><?php echo __('Model'); ?></th>
		<td>
			<?php echo h($draft['Draft']['model']); ?>
			&nbsp;
		</td></tr>
		<tr><th><?php echo __('User Id'); ?></th>
		<td>
			<?php echo h($draft['Draft']['user_id']); ?>
			&nbsp;
		</td></tr>
		<tr><th><?php echo __('Created'); ?></th>
		<td>
			<?php echo h($draft['Draft']['created']); ?>
			&nbsp;
		</td></tr>
		<tr><th><?php echo __('Modified'); ?></th>
		<td>
			<?php echo h($draft['Draft']['modified']); ?>
			&nbsp;
		</td></tr>
		<tr><th><?php echo __('Json'); ?></th>
		<td>
			<?php var_dump(json_decode($draft['Draft']['json'], true)); ?>
			&nbsp;
		</td></tr>
	</table>
</div>
