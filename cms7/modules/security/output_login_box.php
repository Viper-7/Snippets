<div class="module output security login_box">
	<form method="POST" action="<?php echo Config::url('login'); ?>">
		<fieldset>
			<legend>Login</legend>

			<label for="security_login_username">Username:</label>
			<input type="text" name="username" id="security_login_username" size="20" /><br/>
			
			
			<label for="security_login_password">Password:</label>
			<input type="password" name="password" id="security_login_password" size="20" /><br/>
			
			<input type="submit" name="submit" value="Login" />
		</fieldset>
	</form>
</div>