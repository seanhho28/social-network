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
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $profileName . '\'s Friends'; ?></title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<div id="sidebar" class="right">
				<ul>
					<li><a href="index.php"><a href="profile.php?user=<?php echo $username; ?>">Profile</a></li>
					<li class="selected-option"><a href="friends.php?user=<?php echo $username; ?>">Friends</a></li>
				</ul>
			</div>
			<div id="main-container" class="profile friends">
				<h2 class="title"><?php echo $profileName, '\'s '; ?>Friends</h2>
				<?php include 'includes/alerts.php'; ?>
				<div id="all-friends">
					<?php if($display['friends']) : ?>
						<?php if($profile->getFriends()) : $friends = $profile->friends(); ?>
							<?php foreach($friends as $friend) : $friendName = $friend->firstName . ' ' . $friend->middleName . ' ' . $friend->lastName; ?>
								<div class="friend">
									<div class="friend-picture">
										<a href="profile.php?user=<?php echo $friend->username; ?>"><img src="<?php echo $friend->profilePicture; ?>"></a>
									</div>
									<span class="name"><a href="profile.php?user=<?php echo $friend->username; ?>"><?php echo $friendName; ?></a></span>
								</div>
							<?php endforeach; ?>
							<?php else : ?>
								<p>No friends.</p>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>