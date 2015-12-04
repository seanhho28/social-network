<?php
	require_once 'core/init.php';
	
	$user = new User();

	if(!$user->isLoggedIn()) {
		Redirect::to('index.php');
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Friends</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<div id="sidebar" class="left">
				<ul>
					<li><a href="./">Recent Activity</a></li>
					<li><a href="friend-requests.php">Friend Requests</a></li>
					<li class="selected-option"><a href="my-friends.php">Friends</a></li>
					<li><a href="search.php">Search</a></li>
				</ul>
			</div>
			<div id="main-container" class="page friends">
				<h2 class="title">My Friends</h2>
				<?php include 'includes/alerts.php'; ?>
				<div id="search">
					<form action="search.php" method="get">
						<table>
							<tr>
								<td>
									<label for="username">Username</label>
								</td>
								<td>
									<label for="firstName">First Name</label>
								</td>
								<td>
									<label for="middleName">Middle Name</label>
								</td>
								<td>
									<label for="lastName">Last Name</label>
								</td>
							</tr>
							<tr>
								<td>
									<input id="username" type="text" name="username" autocomplete="off">
								</td>
								<td>
									<input id="firstName" type="text" name="firstName" autocomplete="off">
								</td>
								<td>
									<input id="middleName" type="text" name="middleName" autocomplete="off">
								</td>
								<td>
									<input id="lastName" type="text" name="lastName" autocomplete="off">
								</td>
							</tr>
							<tr>
								<td>
									<input type="submit" name="search" value="Search">
								</td>
								<td>
									<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
								</td>
							</tr>
						</table>
					</form>
				</div>
				<h3 class="subtitle"></h3>
				<div id="friends">
					<div id="all-friends">
						<?php if($user->getFriends()) : $friends = $user->friends(); ?>
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
					</div>
				</div>
			</div>
		</div>
	</body>
</html>