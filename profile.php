<?php
	require_once 'core/init.php';
	
	$user = new User();

	if(!$username = Input::get('user')) {
		Redirect::to('index.php');
	}
	else {
		$profile = new User($username);
		
		if(!$profile->exists()) {
			Redirect::to(404);
		}
	}

	$profileName = $profile->data()->firstName . ' ' . $profile->data()->middleName . ' ' . $profile->data()->lastName;

	$birthdayBefore = new DateTime($profile->data()->birthday);
	$profileBirthday = $birthdayBefore->format('F j, Y');

	if($profile->data()->gender === 'm') {
		$profileGender = 'Male';
	}
	else if($profile->data()->gender === 'f') {
		$profileGender = 'Female';
	}

	// 0 = false/don't display
	// 1 = true/display
	$privacy = $profile->getPrivacy();

	foreach($privacy as $key => $value) {
		switch($value) {
			case 'p':
				$display[$key] = 1;
			break;
			case 'f':
				if($user->isLoggedIn()) {
					if($profile->isFriend($user->data()->id) || ($profile->data()->id === $user->data()->id)) {
						$display[$key] = 1;
						break;
					}
				}

				$display[$key] = 0;
			break;
			case 'm':
				if($user->isLoggedIn()) {
					if($profile->data()->id === $user->data()->id) {
						$display[$key] = 1;
						break;
					}
				}

				$display[$key] = 0;
			break;
		}
	}

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
						/*
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'notice',
								'title' => 'Hello ' . $visitor->data()->firstName
							),
							'alerts' => array(
								'Welcome back.'
							)
						));
						*/
						
						Redirect::to('profile.php?user=' . $username);
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

						Redirect::to('login.php?ref=' . $profile->data()->username);
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

					Redirect::to('login.php?ref=' . $profile->data()->username);
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

				Redirect::to('login.php?ref=' . $profile->data()->username);
				*/
			//}
		}
		else if(isset($_POST['post'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'comment' => array(
						'required' => true,
						'max' => 750
					)
				));

				if($validation->passed()) {
					$post = new Post();

					try {
						$post->set(array(
							'uid' => $profile->data()->id,
							'fid' => $user->data()->id,
							'comment' => Input::get('comment'),
							'timestamp' => date('Y-m-d H:i:s')
						));

						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Success'
							),
							'alerts' => array(
								"You posted on {$profileName}'s profile."
							)
						));

						Redirect::to('profile.php?user=' . $username);
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
		else if(isset($_POST['update_profile'])) {
			//if(Token::check(Input::get('token'))) {
				Redirect::to('settings.php');
			//}
		}
		else if(isset($_POST['privacy_settings'])) {
			//if(Token::check(Input::get('token'))) {
				Redirect::to('privacy.php');
			//}
		}
		else if(isset($_POST['request_friend'])) {
			//if(Token::check(Input::get('token'))) {
				$fid = $_POST['profile'];

				if(!$user->isFriend($fid) && !$user->getFriendRequests($fid) && !$profile->getFriendRequests($user->data()->id)) {
					try {
						$user->requestFriend($fid);
					
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Friend Requested'
							),
							'alerts' => array(
								"You sent {$profileName} a friend request."
							)
						));

						Redirect::to('profile.php?user=' . $username);
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
		else if(isset($_POST['confirm_friend_request'])) {
			//if(Token::check(Input::get('token'))) {
				$fid = $_POST['profile'];

				if(!$user->isFriend($fid) && $user->getFriendRequests($fid)) {
					try {
						$user->addFriend($fid);
					
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Request Accepted'
							),
							'alerts' => array(
								"You are now friends with {$profileName}."
							)
						));

						Redirect::to('profile.php?user=' . $username);
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
		else if(isset($_POST['unfriend'])) {
			//if(Token::check(Input::get('token'))) {
				$fid = $_POST['profile'];

				if($user->isFriend($fid)) {
					try {
						$user->unfriend($fid);
					
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Unfriended'
							),
							'alerts' => array(
								"You unfriended {$profileName}."
							)
						));

						Redirect::to('profile.php?user=' . $username);
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
		else if(isset($_POST['delete_friend_request'])) {
			//if(Token::check(Input::get('token'))) {
				$fid = $_POST['profile'];

				if(!$user->isFriend($fid) && $user->getFriendRequests($fid)) {
					try {
						$request = new Request();

						$request->delete('friend', array(
							'uid' => $user->data()->id,
							'fid' => $fid
						));
					
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Request Deleted'
							),
							'alerts' => array(
								"You deleted {$profileName}'s friend request."
							)
						));

						Redirect::to('profile.php?user=' . $username);
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
		else if(isset($_POST['retract_friend_request'])) {
			//if(Token::check(Input::get('token'))) {
				$fid = $_POST['profile'];

				if(!$user->isFriend($fid) && $profile->getFriendRequests($user->data()->id)) {
					try {
						$request = new Request();

						$request->delete('friend', array(
							'uid' => $fid,
							'fid' => $user->data()->id
						));
					
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Request Retracted'
							),
							'alerts' => array(
								"You retracted your friend request to {$profileName}."
							)
						));

						Redirect::to('profile.php?user=' . $username);
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $profileName . '\'s Profile'; ?></title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<?php if(Session::exists('alerts')) : $alerts = Session::flash('alerts'); ?>
			<div id="alert-container" class="<?php echo $alerts['info']['type']; ?> profile">
				<h3><?php echo $alerts['info']['title']; ?></h3>
				<?php foreach($alerts['alerts'] as $alert): ?>
					<p><?php echo $alert; ?></p>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<div id="main-container" class="profile">
				<div id="picture" class="panel">
					<?php
						if($display['profilePicture']) {
							echo "<img src=\"{$profile->data()->profilePicture}\" alt=\"Profile Picture\">";
						}
						else {
							echo "<img src=\"images/profile/default.png\" alt=\"Profile Picture\">";
						}
					?>
				</div>
				<div id="info-box">
					<div id="friend-buttons">
						<?php if($user->isLoggedIn()) : ?>
							<?php if($user->data()->id === $profile->data()->id) : ?>
								<form action="" method="post">
									<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
									<input type="submit" name="update_profile" value="Update Profile">
									<input type="submit" name="privacy_settings" value="Privacy Settings">
								</form>
							<?php else : ?>
								<?php if(!$user->isFriend($profile->data()->id)) : ?>
									<?php if(!$user->getFriendRequests($profile->data()->id) && !$profile->getFriendRequests($user->data()->id)) : ?>
										<form action="" method="post">
											<input type="hidden" name="profile" value="<?php echo $profile->data()->id; ?>">
											<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
											<input type="submit" name="request_friend" value="Add Friend">
										</form>
									<?php else : ?>
										<?php if($profile->getFriendRequests($user->data()->id)) : ?>
											<form action="" method="post">
												<input type="hidden" name="profile" value="<?php echo $profile->data()->id; ?>">
												<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
												<input type="submit" name="retract_friend_request" value="Retract Request">
											</form>
										<?php else : ?>
											<form action="" method="post">
												<input type="hidden" name="profile" value="<?php echo $profile->data()->id; ?>">
												<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
												<input type="submit" name="confirm_friend_request" value="Confirm">
												<input type="submit" name="delete_friend_request" value="Delete Request">
											</form>
										<?php endif; ?>
									<?php endif; ?>
								<?php else : ?>
									<form action="" method="post">
										<input type="hidden" name="profile" value="<?php echo $profile->data()->id; ?>">
										<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
										<input type="submit" name="unfriend" value="Unfriend">
									</form>
								<?php endif; ?>
							<?php endif; ?>
							
						<?php endif; ?>
					</div>
					<div id="info" class="panel">
						<h1 class="profile"><?php echo "<a href=\"profile.php?user={$username}\">{$profileName}</a>"; ?></h1>
						<div id="more-info">
							<?php if($display['aboutMe']) echo '<p>', $profile->data()->aboutMe, '</p>'; ?>
						</div>
					</div>
				</div>
				<div id="left-column">
					<div id="about" class="panel">
						<h3 class="subtitle">About</h3>
						<p class="about"><?php if($display['occupation'] && !empty($profile->data()->occupation)) echo "<span class=\"about\">{$profile->data()->occupation}</span>"; ?><?php if($display['occupation'] && !empty($profile->data()->work)) echo " at <span class=\"about\">{$profile->data()->work}</span>"; ?></p>
						<p class="about"><?php if($display['hometown'] && !empty($profile->data()->hometown)) echo "From <span class=\"about\">{$profile->data()->hometown}</span>"; ?></p>
						<p class="about"><?php if($display['currentCity'] && !empty($profile->data()->currentCity)) echo "Lives in <span class=\"about\">{$profile->data()->currentCity}</span>"; ?></p>
						<p class="about"><?php if($display['school'] && !empty($profile->data()->school)) echo "Goes to <span class=\"about\">{$profile->data()->school}</span>"; ?></p>
						<p class="about"><?php if($display['birthday'] && !empty($profile->data()->birthday)) echo "Born on <span class=\"about\">{$profileBirthday}</span>"; ?></p>
						<p class="about"><?php if($display['gender'] && !empty($profile->data()->gender)) echo "<span class=\"about\">{$profileGender}</span>"; ?></p>
					</div>
					<div id="friends" class="panel">
						<h3 class="subtitle">Friends</h3>
						<?php if($display['friends']) : ?>
							<?php if($profile->getFriends()) : $friends = $profile->friends(); ?>
								<?php foreach(array_slice($friends, 0, 9) as $friend) : $friendName = $friend->firstName . ' ' . $friend->middleName . ' ' . $friend->lastName; ?>
									<div class="friend">
										<div class="friend-picture">
											<a href="profile.php?user=<?php echo $friend->username; ?>"><img src="<?php echo $friend->profilePicture; ?>"></a>
										</div>
										<span class="name"><a href="profile.php?user=<?php echo $friend->username; ?>"><?php echo $friendName; ?></a></span>
									</div>
								<?php endforeach; ?>
								<?php if(count($friends) > 9) : ?>
									<div class="view-all">
										<a href="friends.php?user=<?php echo $username; ?>"><span>View All</span></a>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
				<div id="main">
					<div id="post" class="panel">
						<?php if($user->isLoggedIn()) : ?>
							<?php if($user->isFriend($profile->data()->id) || ($user->data()->id === $profile->data()->id)) : ?>
								<h3 class="subtitle">Write something...</h3>
								<form action="" method="post" id="post-form">
									<table>
										<tr>
											<td>
												<textarea form="post-form" name="comment" maxlength="750"></textarea>
											</td>										
										</tr>
										<tr>
											<td>
												<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
											</td>
										</tr>
										<tr>
											<td>
												<input type="submit" name="post" value="Post">
											</td>
										</tr>
									</table>
								</form>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<div id="comments" class="panel">
						<?php if($display['comments']) : $posts = new Post(); ?>
							<?php if($posts->get($profile->data()->id)) : ?>
								<h3 class="subtitle">Comments</h3>
								<?php foreach($posts->posts() as $post) : ?>
									<div class="comment">
										<div class="picture">
											<a href="profile.php?user=<?php echo $post['username']; ?>"><img src="<?php echo $post['profilePicture']; ?>"></a>
										</div>
										<div class="message-box">
											<h4 class="name"><?php echo "<a href=\"profile.php?user={$post['username']}\">{$post['name']}</a>"; ?></h4>
											<p class="message"><?php echo $post['comment']; ?></p>
											<p class="timestamp"><?php echo $post['timestamp']; ?></p>
										</div>
										<br clear="all">
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<p>No comments.</p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div id="sidebar" class="right">
				<ul>
					<li class="selected-option"><a href="index.php"><a href="profile.php?user=<?php echo $username; ?>">Profile</a></li>
					<li><a href="friends.php?user=<?php echo $username; ?>">Friends</a></li>
				</ul>
			</div>
		</div>
	</body>
</html>