<div id="topbar">
	<div class="wrapper">
		<div id="topbar-logo">
			<a href="./">SOCIAL NETWORK</a>
		</div>
		<?php if(!$user->isLoggedIn()): ?>
			<div id="topbar-login">
				<form action="" method="post">
					<table>
						<tr>
							<td>
								<label class="textfield" for="login-username">Username</label>
							</td>
							<td>
								<label class="textfield" for="login-password">Password</label>
							</td>
						</tr>
						<tr>
							<td>
								<input type="text" name="login-username" id="login-username" maxlength="20" autocomplete="off">
							</td>
							<td>
								<input type="password" name="login-password" id="login-password" maxlength="20" autocomplete="off">
							</td>
							<td>
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							</td>
							<td>
								<input type="submit" name="login" value="Log In">
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="remember" id="remember" value="y">
								<label class="checkbox" for="remember">Keep me logged in</label>
							</td>
						</tr>
					</table>
				</form>
			</div>
		<?php else: ?>
			<div id="topbar-nav">
				<ul>
					<li><a href="profile.php?user=<?php echo $user->data()->username; ?>"><?php echo $user->data()->firstName . ' ' . $user->data()->middleName . ' ' . $user->data()->lastName; ?></a></li>
					<li><a href="./">Home</a></li>
					<li><a href="">Account</a>
						<ul>
							<li><a href="settings.php">Update Profile</a></li>
							<li><a href="privacy.php">Privacy Settings</a></li>
							<li><a href="logout.php">Log Out</a></li>
						</ul>
					</li>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</div>