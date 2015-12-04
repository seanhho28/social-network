<?php
	require_once 'core/init.php';

	$user = new User();

	if(Input::exists()) {
		if(isset($_POST['login'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'username' => array(
						'required' => true
					),
					'password' => array(
						'required' => true
					)
				));
				
				if($validation->passed()) {
					$visitor = new User();
					$remember = (Input::get('remember') === 'y') ? true : false;
					$login = $visitor->login(Input::get('username'), Input::get('password'), $remember);
					
					if($login) {
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'notice',
								'title' => 'Hello ' . $visitor->data()->firstName
							),
							'alerts' => array(
								'Welcome back.'
							)
						));

						if($ref = Input::get('ref')) {
							$profile = new User($ref);

							if($profile->exists()) {
								Redirect::to('profile.php?user=' . $ref);
							}
						}

						Redirect::to('index.php');
					}
					else {
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'error',
								'title' => 'Oops!'
							),
							'alerts' => array(
								'Incorrect username or password.'
							)
						));
					}
				}
				else {
					Session::flash('alerts', array(
						'info' => array(
							'type' => 'error',
							'title' => 'Oops!'
						),
						'alerts' => $validation->errors()
					));
				}
			//}
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Social Network</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<div id="topbar">
			<div class="wrapper">
				<div id="topbar-logo">
					<a href="index.php">SOCIAL NETWORK</a>
				</div>
			</div>
		</div>
		<div id="main-wrapper" class="wrapper">
			<div id="center-container">
				<h2 class="title">Log In</h2>
				<?php include 'includes/alerts.php'; ?>
				<form action="" method="post">
					<table>
						<tr>
							<td>
								<label class="textfield" for="username">Username:</label>
							</td>
							<td>
								<input id="username" type="text" name="username" maxlength="20" autocomplete="off"> 
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="password">Password:</label>
							</td>
							<td>
								<input id="password" type="password" name="password" maxlength="20" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input id="remember" type="checkbox" name="remember" value="y">
								<label class="checkbox" for="remember">Keep me logged in</label>
							</td>
						</tr>
						<tr>
							<td>
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							</td>
							<td>
								<input type="submit" name="login" value="Log In"> or <a class="tiny-link" href="index.php">Sign up</a>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</body>
</html>