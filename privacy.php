<?php
	require_once 'core/init.php';
	
	$user = new User();

	if(!$user->isLoggedIn()) {
		Redirect::to('index.php');
	}

	if(Input::exists()) {
		if(isset($_POST['update-privacy'])) {
			//if(Token::check(Input::get('token'))) {
				try {
					$user->updatePrivacy(array(
						'profilePicture' => Input::get('profile-picture'),
						'aboutMe' => Input::get('about-me'),
						'friends' => Input::get('friends'),
						'comments' => Input::get('comments'),
						'occupation' => Input::get('occupation'),
						'hometown' => Input::get('hometown'),
						'currentCity' => Input::get('current-city'),
						'school' => Input::get('school'),
						'birthday' => Input::get('birthday'),
						'gender' => Input::get('gender')
					));
					
					Session::flash('alerts', array(
						'info' => array(
							'type' => 'success',
							'title' => 'Updated'
						),
						'alerts' => array(
							'Your privacy settings have been successfully updated.'
						)
					));

					Redirect::to('privacy.php');
				}
				catch(Exception $e) {
					die($e->getMessage());
				}
			//}
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Privacy Settings</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<div id="sidebar" class="left"></div>
			<div id="main-container" class="page">
				<h2 class="title">Privacy Settings</h2>
				<?php include 'includes/alerts.php'; ?>
				<?php $privacy = $user->getPrivacy(); ?>
				<form action="" method="post">
					<table>
						<tr>
							<td>
								<label class="textfield" for="profile-picture">Profile Picture:</label>
							</td>
							<td>
								<select id="profile-picture" name="profile-picture">
									<option value="p" <?php if($privacy->profilePicture === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->profilePicture === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->profilePicture === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="about-me">About Me:</label>
							</td>
							<td>
								<select id="about-me" name="about-me">
									<option value="p" <?php if($privacy->aboutMe === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->aboutMe === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->aboutMe === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="friends-privacy">Friends:</label>
							</td>
							<td>
								<select id="friends-privacy" name="friends">
									<option value="p" <?php if($privacy->friends === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->friends === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->friends === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="about-me">Comments:</label>
							</td>
							<td>
								<select id="comments" name="comments">
									<option value="p" <?php if($privacy->comments === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->comments === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->comments === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
						<tr>
							<td>
								<label class="textfield" for="occupation">Occupation:</label>
							</td>
							<td>
								<select id="occupation" name="occupation">
									<option value="p" <?php if($privacy->occupation === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->occupation === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->occupation === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="hometown">Hometown:</label>
							</td>
							<td>
								<select id="hometown" name="hometown">
									<option value="p" <?php if($privacy->hometown === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->hometown === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->hometown === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="current-city">Current City:</label>
							</td>
							<td>
								<select id="current-city" name="current-city">
									<option value="p" <?php if($privacy->currentCity === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->currentCity === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->currentCity === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="school">School:</label>
							</td>
							<td>
								<select id="school" name="school">
									<option value="p" <?php if($privacy->school === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->school === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->school === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="birthday">Birthday:</label>
							</td>
							<td>
								<select id="birthday" name="birthday">
									<option value="p" <?php if($privacy->birthday === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->birthday === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->birthday === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="gender">Gender:</label>
							</td>
							<td>
								<select id="gender" name="gender">
									<option value="p" <?php if($privacy->gender === 'p') echo 'selected'; ?>>Public</option>
									<option value="f" <?php if($privacy->gender === 'f') echo 'selected'; ?>>Friends</option>
									<option value="m" <?php if($privacy->gender === 'm') echo 'selected'; ?>>Me</option>
								</select>
							</td>
						</tr>
							<td>
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							</td>
							<td>
								<input type="submit" name="update-privacy" value="Save">
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</body>
</html>