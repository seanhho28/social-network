
<?php if(Session::exists('alerts')) : $alerts = Session::flash('alerts'); ?>
	<div id="alert-container" class="<?php echo $alerts['info']['type']; ?> page">
		<h3><?php echo $alerts['info']['title']; ?></h3>
		<?php foreach($alerts['alerts'] as $alert): ?>
			<p><?php echo $alert; ?></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>