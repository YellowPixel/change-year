<div class="wrap">
 <h1>Change year</h1>
 <form action="" method="get">
	<input type="text" name="old_year" placeholder="Old year" pattern="[0-9]{4}" required>
	<input type="text" name="new_year" placeholder="New year" pattern="[0-9]{4}" required>
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('update_year'); ?>">
	<input type="submit" value="Replace">
</form>
</div>