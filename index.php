<?php
	require_once 'core/init.php'; 

	$user = new User();

	if(Input::exists()) {
		if(isset($_POST['login'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'login-username' => array(
						'required' => true
					),
					'login-password' => array(
						'required' => true
					)
				));
				
				if($validation->passed()) {
					$visitor = new User();
					$remember = (Input::get('remember') === 'y') ? true : false;
					$login = $visitor->login(Input::get('login-username'), Input::get('login-password'), $remember);
					
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

						Redirect::to('login.php');
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

					Redirect::to('login.php');
				}
			//}
			//else {
				/*
				Session::flash('alerts', array(
					'info' => array(
						'type' => 'error',
						'title' => 'Oops!'
					),
					'alerts' => array(
						'Token check failed (this is a bug).'
					)
				));

				Redirect::to('login.php');
				*/
			//}
		}
		else if(isset($_POST['signup'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'first-name' => array(
						'required' => true,
						'min' => 2,
						'max' => 20
					),
					'last-name' => array(
						'required' => true,
						'min' => 2,
						'max' => 20
					),
					'signup-username' => array(
						'required' => true,
						'min' => 2,
						'max' => 20,
						'unique' => 'users'
					),
					'email' => array(
						'required' => true,
						'max' => 64,
						'type' => 'email',
						'unique' => 'users'
					),
					'signup-password' => array(
						'required' => true,
						'min' => 6
					),
					'signup-password-confirm' => array(
						'required' => true,
						'min' => 6,
						'matches' => 'signup-password'
					),
					'birthday-month' => array(
						'required' => true,
					),
					'birthday-day' => array(
						'required' => true,
					),
					'birthday-year' => array(
						'required' => true,
					)
				));
				
				if($validation->passed()) {
					$visitor = new User();
					$salt = Hash::salt(32);
					
					try {
						$visitor->create(array(
							'username' => Input::get('signup-username'),
							'firstName' => ucwords(strtolower(Input::get('first-name'))),
							'lastName' => ucwords(strtolower(Input::get('last-name'))),
							'email' => Input::get('email'),
							'password' => Hash::make(Input::get('signup-password'), $salt),
							'salt' => $salt,
							'birthday' => Input::get('birthday-year') . '-' . Input::get('birthday-month') . '-' . Input::get('birthday-day'),
							'gender' => Input::get('gender'),
							'joined' => date('Y-m-d H:i:s'),
						));
						
						$login = $visitor->login(Input::get('signup-username'), Input::get('signup-password'));

						if($login) {
							Session::flash('alerts', array(
								'info' => array(
									'type' => 'notice',
									'title' => 'Welcome!'
								),
								'alerts' => array(
									'Thanks for signing up! You may update your profile now or <a href="index.php">later</a>.'
								)
							));

							Redirect::to('settings.php');
						}
						else {
							Session::flash('alerts', array(
								'info' => array(
									'type' => 'error',
									'title' => 'Oops!'
								),
								'alerts' => array(
									'Log in after sign up failed.'
								)
							));
						}
					}
					catch(Exception $e) {
						die($e->getMessage());
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
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<?php if($user->isLoggedIn()) : ?>
				<div id="sidebar" class="left">
					<ul>
						<li class="selected-option"><a href="./">Recent Activity</a></li>
						<li><a href="friend-requests.php">Friend Requests</a></li>
						<li><a href="my-friends.php">Friends</a></li>
						<li><a href="search.php">Search</a></li>
					</ul>
				</div>
				<div id="main-container" class="page">
					<h2 class="title">Recent Activity</h2>
					<?php include 'includes/alerts.php'; ?>
					<?php $activity = new Notification(); if($activity->get($user->data()->id)) : $notifications = $activity->notifications(); ?>
						<?php foreach($notifications as $notification) : ?>
							<div class="notification">
								<div class="picture">
									<a href="profile.php?user=<?php echo $notification['username']; ?>"><img src="<?php echo $notification['profilePicture']; ?>"></a>
								</div>
								<div class="message">
									<p class="message"><?php echo $notification['message']; ?></p>
									<p class="timestamp"><?php echo $notification['timestamp']; ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p>No notifications.</p>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div id="center-container">
					<h2 class="title">Sign Up</h2>
					<?php include 'includes/alerts.php'; ?>
					<form action="" method="post">
						<table>
							<tr>
								<td>
									<label class="textfield" for="first-name">First Name:</label>
								</td>
								<td>
									<input id="first-name" type="text" name="first-name" maxlength="20" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<label class="textfield" for="last-name">Last Name:</label>
								</td>
								<td>
									<input id="last-name" type="text" name="last-name" maxlength="20" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<label class="textfield" for="signup-username">Username:</label>
								</td>
								<td>
									<input id="signup-username" type="text" name="signup-username" maxlength="20" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<label class="textfield" for="email">Email:</label>
								</td>
								<td>
									<input id="email" type="text" name="email" maxlength="64" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<label class="textfield" for="signup-password">Password:</label>
								</td>
								<td>
									<input id="signup-password" type="password" name="signup-password" maxlength="20" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<label class="textfield" for="signup-password-confirm">Re-enter Password:</label>
								</td>
								<td>
									<input id="signup-password-confirm" type="password" name="signup-password-confirm" maxlength="20" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<label class="textfield" for="birthday">Birthday:</label>
								</td>
								<td>
									<select id="birthday" name="birthday-month">
										<option value="0" selected>Month</option>
										<option value="1">Jan</option>
										<option value="2">Feb</option>
										<option value="3">Mar</option>
										<option value="4">Apr</option>
										<option value="5">May</option>
										<option value="6">Jun</option>
										<option value="7">Jul</option>
										<option value="8">Aug</option>
										<option value="9">Sep</option>
										<option value="10">Oct</option>
										<option value="11">Nov</option>
										<option value="12">Dec</option>
									</select>
									<select name="birthday-day">
										<option value="0" selected>Day</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
										<option value="24">24</option>
										<option value="25">25</option>
										<option value="26">26</option>
										<option value="27">27</option>
										<option value="28">28</option>
										<option value="29">29</option>
										<option value="30">30</option>
										<option value="31">31</option>
									</select>
									<select name="birthday-year">
										<option value="0" selected>Year</option>
										<option value="2014">2014</option>
										<option value="2013">2013</option>
										<option value="2012">2012</option>
										<option value="2011">2011</option>
										<option value="2010">2010</option>
										<option value="2009">2009</option>
										<option value="2008">2008</option>
										<option value="2007">2007</option>
										<option value="2006">2006</option>
										<option value="2005">2005</option>
										<option value="2004">2004</option>
										<option value="2003">2003</option>
										<option value="2002">2002</option>
										<option value="2001">2001</option>
										<option value="2000">2000</option>
										<option value="1999">1999</option>
										<option value="1998">1998</option>
										<option value="1997">1997</option>
										<option value="1996">1996</option>
										<option value="1995">1995</option>
										<option value="1994">1994</option>
										<option value="1993">1993</option>
										<option value="1992">1992</option>
										<option value="1991">1991</option>
										<option value="1990">1990</option>
										<option value="1989">1989</option>
										<option value="1988">1988</option>
										<option value="1987">1987</option>
										<option value="1986">1986</option>
										<option value="1985">1985</option>
										<option value="1984">1984</option>
										<option value="1983">1983</option>
										<option value="1982">1982</option>
										<option value="1981">1981</option>
										<option value="1980">1980</option>
										<option value="1979">1979</option>
										<option value="1978">1978</option>
										<option value="1977">1977</option>
										<option value="1976">1976</option>
										<option value="1975">1975</option>
										<option value="1974">1974</option>
										<option value="1973">1973</option>
										<option value="1972">1972</option>
										<option value="1971">1971</option>
										<option value="1970">1970</option>
										<option value="1969">1969</option>
										<option value="1968">1968</option>
										<option value="1967">1967</option>
										<option value="1966">1966</option>
										<option value="1965">1965</option>
										<option value="1964">1964</option>
										<option value="1963">1963</option>
										<option value="1962">1962</option>
										<option value="1961">1961</option>
										<option value="1960">1960</option>
										<option value="1959">1959</option>
										<option value="1958">1958</option>
										<option value="1957">1957</option>
										<option value="1956">1956</option>
										<option value="1955">1955</option>
										<option value="1954">1954</option>
										<option value="1953">1953</option>
										<option value="1952">1952</option>
										<option value="1951">1951</option>
										<option value="1950">1950</option>
										<option value="1949">1949</option>
										<option value="1948">1948</option>
										<option value="1947">1947</option>
										<option value="1946">1946</option>
										<option value="1945">1945</option>
										<option value="1944">1944</option>
										<option value="1943">1943</option>
										<option value="1942">1942</option>
										<option value="1941">1941</option>
										<option value="1940">1940</option>
										<option value="1939">1939</option>
										<option value="1938">1938</option>
										<option value="1937">1937</option>
										<option value="1936">1936</option>
										<option value="1935">1935</option>
										<option value="1934">1934</option>
										<option value="1933">1933</option>
										<option value="1932">1932</option>
										<option value="1931">1931</option>
										<option value="1930">1930</option>
										<option value="1929">1929</option>
										<option value="1928">1928</option>
										<option value="1927">1927</option>
										<option value="1926">1926</option>
										<option value="1925">1925</option>
										<option value="1924">1924</option>
										<option value="1923">1923</option>
										<option value="1922">1922</option>
										<option value="1921">1921</option>
										<option value="1920">1920</option>
										<option value="1919">1919</option>
										<option value="1918">1918</option>
										<option value="1917">1917</option>
										<option value="1916">1916</option>
										<option value="1915">1915</option>
										<option value="1914">1914</option>
										<option value="1913">1913</option>
										<option value="1912">1912</option>
										<option value="1911">1911</option>
										<option value="1910">1910</option>
										<option value="1909">1909</option>
										<option value="1908">1908</option>
										<option value="1907">1907</option>
										<option value="1906">1906</option>
										<option value="1905">1905</option>
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<div id="gender">
										<label for="male">Male</label><input id="male" type="radio" name="gender" value="m" checked>
										<label for="female">Female</label><input id="female" type="radio" name="gender" value="f">
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
								</td>
								<td>
									<input type="submit" name="signup" value="Sign Up">
								</td>
							</tr>
						</table>
					</form>
				</div>
			<?php endif; ?>
		</div>
	</body>
</html>