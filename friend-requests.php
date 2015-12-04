<?php
	require_once 'core/init.php';

	$user = new User();

	if(!$user->isLoggedIn()) {
		Redirect::to('index.php');
	}

	if(Input::exists()) {
		if(isset($_POST['confirm_friend_request'])) {
			//if(Token::check(Input::get('token'))) {
				$friend = new User($_POST['friend']);
				$fid = $friend->data()->id;
				$friendName = $friend->data()->firstName . ' ' . $friend->data()->middleName . ' ' . $friend->data()->lastName;

				if(!$user->isFriend($fid) && $user->getFriendRequests($fid)) {
					try {
						$user->addFriend($fid);
						
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Request Accepted'
							),
							'alerts' => array(
								"You are now friends with {$friendName}."
							)
						));
					}
					catch(Exception $e) {
						die($e->getMessage());
					}
				}
			//}
		}
		else if(isset($_POST['delete_friend_request'])) {
			//if(Token::check(Input::get('token'))) {
				$friend = new User($_POST['friend']);
				$fid = $friend->data()->id;
				$friendName = $friend->data()->firstName . ' ' . $friend->data()->middleName . ' ' . $friend->data()->lastName;

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
								"You deleted {$friendName}'s friend request."
							)
						));
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
		<title>Friend Requests</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<div id="sidebar" class="left">
				<ul>
					<li><a href="./">Recent Activity</a></li>
					<li class="selected-option"><a href="friend-requests.php">Friend Requests</a></li>
					<li><a href="my-friends.php">Friends</a></li>
					<li><a href="search.php">Search</a></li>
				</ul>
			</div>
			<div id="main-container" class="page">
				<h2 class="title">Friend Requests</h2>
				<?php include 'includes/alerts.php'; ?>
				<?php $requests = new Request(); if($requests->get('friend', $user->data()->id)) : $friendRequests = $requests->requests(); ?>
					<?php foreach($friendRequests as $friendRequest) : ?>
						<div class="friend-request">
							<div class="picture">
								<a href="profile.php?user=<?php echo $friendRequest['username']; ?>"><img src="<?php echo $friendRequest['profilePicture']; ?>"></a>
							</div>
							<div class="buttons">
								<h3 class="name"><?php echo "<a href=\"profile.php?user={$friendRequest['username']}\">{$friendRequest['name']}</a>"; ?></h3>
								<form action="" method="post">
									<input type="hidden" name="friend" value="<?php echo $friendRequest['fid']; ?>">
									<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
									<input type="submit" name="confirm_friend_request" value="Confirm">
									<input type="submit" name="delete_friend_request" value="Delete Request">
								</form>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p>No friend requests.</p>
				<?php endif; ?>
			</div>
		</div>
	</body>
</html>