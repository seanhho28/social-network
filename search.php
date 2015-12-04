<?php
	require_once 'core/init.php';

	$user = new User();

	if(!$user->isLoggedIn()) {
		Redirect::to('index.php');
	}

	if(Input::exists('get')) {
		$fields = array(
			'username' => Input::get('username'),
			'firstName' => Input::get('firstName'),
			'middleName' => Input::get('middleName'),
			'lastName' => Input::get('lastName')
		);

		$search = new Search();

		$input = false;

		foreach($fields as $field => $value) {
			if(!empty($value)) {
				$input = true;
			}
		}

		if($input) {
			if($search->search($fields)) {
				$results = $search->results();
			}
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Search</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>
		<div id="main-wrapper" class="wrapper">
			<div id="sidebar" class="left">
				<ul>
					<li><a href="./">Recent Activity</a></li>
					<li><a href="friend-requests.php">Friend Requests</a></li>
					<li><a href="my-friends.php">Friends</a></li>
					<li class="selected-option"><a href="search.php">Search</a></li>
				</ul>
			</div>
			<div id="main-container" class="page friends">
				<h2 class="title">Search</h2>
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
						<?php if(!empty($results)) : ?>
							<?php foreach($results as $result) : $resultName = $result->firstName . ' ' . $result->middleName . ' ' . $result->lastName; ?>
								<div class="friend">
									<div class="friend-picture">
										<a href="profile.php?user=<?php echo $result->username; ?>"><img src="<?php echo $result->profilePicture; ?>"></a>
									</div>
									<span class="name"><a href="profile.php?user=<?php echo $result->username; ?>"><?php echo $resultName; ?></a></span>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<p>No results.</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>