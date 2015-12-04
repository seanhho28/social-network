<?php
	require_once 'core/init.php';

	$user = new User();

	if(!$user->isLoggedIn()) {
		Redirect::to('index.php');
	}

	if(Input::exists()) {
		if(isset($_POST['change-picture'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->checkFile($file = $_FILES['profile-picture'], array(
					'type' => 'jpg,jpeg,gif,png',
					'max' => 500000
				));
				
				if($validation->passed()) {
					try {
						$dir = 'images/profile/';
						$fileTmp = $file['tmp_name'];
						$fileExtn = strtolower(end(explode('.', $file['name'])));
						$fileName = substr(md5(time()), 0, 10) . '.' . $fileExtn; // Rename uploaded picture to unique string created from md5 hash of current time
						$filePath = $dir . $fileName;

						move_uploaded_file($fileTmp, $filePath);

						$user->update(array(
							'profilePicture' => $filePath
						));

						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Updated'
							),
							'alerts' => array(
								'Your profile picture has been updated.'
							)
						));

						Redirect::to('settings.php');
					}
					catch(Exception $e) {
						die($e->getMessage);
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

					Redirect::to('settings.php');
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
				*/
			//}
		}
		else if(isset($_POST['update-profile'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'first-name' => array(
						'required' => true,
						'min' => 2,
						'max' => 20
					),
					'middle-name' => array(
						'max' => 20
					),
					'last-name' => array(
						'required' => true,
						'min' => 2,
						'max' => 20
					),
					'occupation' => array(
						'max' => 64
					),
					'work' => array(
						'max' => 64
					),
					'hometown' => array(
						'max' => 64
					),
					'current-city' => array(
						'max' => 64
					),
					'school' => array(
						'max' => 64
					),
					'birthday-month' => array(
						'required' => true,
					),
					'birthday-day' => array(
						'required' => true,
					),
					'birthday-year' => array(
						'required' => true,
					),
					'about-me' => array(
						'max' => 750
					)
				));
				
				if($validation->passed()) {
					try {
						$user->update(array(
							'firstName' => ucwords(strtolower(Input::get('first-name'))),
							'middleName' => ucwords(strtolower(Input::get('middle-name'))),
							'lastName' => ucwords(strtolower(Input::get('last-name'))),
							'occupation' => Input::get('occupation'),
							'work' => Input::get('work'),
							'hometown' => Input::get('hometown'),
							'currentCity' => Input::get('current-city'),
							'school' => Input::get('school'),
							'birthday' => Input::get('birthday-year') . '-' . Input::get('birthday-month') . '-' .Input::get('birthday-day'),
							'gender' => Input::get('gender'),
							'aboutMe' => Input::get('about-me')
						));
						
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Updated'
							),
							'alerts' => array(
								'Your profile has been updated.'
							)
						));

						Redirect::to('settings.php');
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
		else if(isset($_POST['change-password'])) {
			//if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'current-password' => array(
						'required' => true,
						'min' => 6
					),
					'new-password' => array(
						'required' => true,
						'min' => 6
					),
					'new-password-confirm' => array(
						'required' => true,
						'matches' => 'new-password'
					)
				));
				
				if($validation->passed()) {
					if(Hash::make(Input::get('current-password'), $user->data()->salt) !== $user->data()->password) {
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'error',
								'title' => 'Oops!'
							),
							'alerts' => array(
								'Incorrect current password.'
							)
						));
					}
					else {
						$salt = Hash::salt(32);
						
						$user->update(array(
							'password' => Hash::make(Input::get('new-password'), $salt),
							'salt' => $salt,
						));
						
						Session::flash('alerts', array(
							'info' => array(
								'type' => 'success',
								'title' => 'Updated'
							),
							'alerts' => array(
								'Your password has been updated.'
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
		<title>Update Profile</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<?php include 'includes/header.php'; ?>			
		<div id="main-wrapper" class="wrapper">
			<div id="sidebar" class="left"></div>
			<div id="main-container" class="page">
				<h2 class="title">Update Profile</h2>
				<?php include 'includes/alerts.php'; ?>
				<h3 class="subtitle">Profile Picture</h3>
				<div id="picture">
					<img src="<?php echo $user->data()->profilePicture; ?>" alt="Profile Picture">
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<table>
						<tr>
							<td>
								<label class="textfield">Profile Picture:</label>
							</td>
							<td>
								<input type="file" name="profile-picture">
							</td>
						</tr>
						<tr>
							<td>
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							</td>
							<td>
								<input type="submit" name="change-picture" value="Save">
							</td>
						</tr>
					</table>
				</form>
				<h3 class="subtitle">General</h3>
				<form id="update-form" action="" method="post">
					<table>
						<tr>
							<td>
								<label class="textfield" for="first-name">First Name:</label>
							</td>
							<td>
								<input id="first-name" type="text" name="first-name" value="<?php echo escape($user->data()->firstName); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="middle-name">Middle Name:</label>
							</td>
							<td>
								<input id="middle-name" type="text" name="middle-name" value="<?php echo escape($user->data()->middleName); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="last-name">Last Name:</label>
							</td>
							<td>
								<input id="last-name" type="text" name="last-name" value="<?php echo escape($user->data()->lastName); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="occupation">Occupation:</label>
							</td>
							<td>
								<input id="occupation" type="text" name="occupation" value="<?php echo escape($user->data()->occupation); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="work">Work:</label>
							</td>
							<td>
								<input id="work" type="text" name="work" value="<?php echo escape($user->data()->work); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="hometown">Hometown:</label>
							</td>
							<td>
								<input id="hometown" type="text" name="hometown" value="<?php echo escape($user->data()->hometown); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="current-city">Current City:</label>
							</td>
							<td>
								<input id="current-city" type="text" name="current-city" value="<?php echo escape($user->data()->currentCity); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="school">School:</label>
							</td>
							<td>
								<input id="school" type="text" name="school" value="<?php echo escape($user->data()->school); ?>" autocomplete="off">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="birthday">Birthday:</label>
							</td>
							<td>
								<?php
									$userBirthday = explode('-', escape($user->data()->birthday));
									$userBirthdayMonth = (int) $userBirthday[1];
									$userBirthdayDay = (int) $userBirthday[2];
									$userBirthdayYear = (int) $userBirthday[0];
								?>
								<select id="birthday" name="birthday-month">
									<option value="0">Month</option>
									<option value="1" <?php if($userBirthdayMonth === 1) echo 'selected'; ?>>Jan</option>
									<option value="2" <?php if($userBirthdayMonth === 2) echo 'selected'; ?>>Feb</option>
									<option value="3" <?php if($userBirthdayMonth === 3) echo 'selected'; ?>>Mar</option>
									<option value="4" <?php if($userBirthdayMonth === 4) echo 'selected'; ?>>Apr</option>
									<option value="5" <?php if($userBirthdayMonth === 5) echo 'selected'; ?>>May</option>
									<option value="6" <?php if($userBirthdayMonth === 6) echo 'selected'; ?>>Jun</option>
									<option value="7" <?php if($userBirthdayMonth === 7) echo 'selected'; ?>>Jul</option>
									<option value="8" <?php if($userBirthdayMonth === 8) echo 'selected'; ?>>Aug</option>
									<option value="9" <?php if($userBirthdayMonth === 9) echo 'selected'; ?>>Sep</option>
									<option value="10" <?php if($userBirthdayMonth === 10) echo 'selected'; ?>>Oct</option>
									<option value="11" <?php if($userBirthdayMonth === 11) echo 'selected'; ?>>Nov</option>
									<option value="12" <?php if($userBirthdayMonth === 12) echo 'selected'; ?>>Dec</option>
								</select>
								<select name="birthday-day">
									<option value="0">Day</option>
									<option value="1" <?php if($userBirthdayDay === 1) echo 'selected'; ?>>1</option>
									<option value="2" <?php if($userBirthdayDay === 2) echo 'selected'; ?>>2</option>
									<option value="3" <?php if($userBirthdayDay === 3) echo 'selected'; ?>>3</option>
									<option value="4" <?php if($userBirthdayDay === 4) echo 'selected'; ?>>4</option>
									<option value="5" <?php if($userBirthdayDay === 5) echo 'selected'; ?>>5</option>
									<option value="6" <?php if($userBirthdayDay === 6) echo 'selected'; ?>>6</option>
									<option value="7" <?php if($userBirthdayDay === 7) echo 'selected'; ?>>7</option>
									<option value="8" <?php if($userBirthdayDay === 8) echo 'selected'; ?>>8</option>
									<option value="9" <?php if($userBirthdayDay === 9) echo 'selected'; ?>>9</option>
									<option value="10" <?php if($userBirthdayDay === 10) echo 'selected'; ?>>10</option>
									<option value="11" <?php if($userBirthdayDay === 11) echo 'selected'; ?>>11</option>
									<option value="12" <?php if($userBirthdayDay === 12) echo 'selected'; ?>>12</option>
									<option value="13" <?php if($userBirthdayDay === 13) echo 'selected'; ?>>13</option>
									<option value="14" <?php if($userBirthdayDay === 14) echo 'selected'; ?>>14</option>
									<option value="15" <?php if($userBirthdayDay === 15) echo 'selected'; ?>>15</option>
									<option value="16" <?php if($userBirthdayDay === 16) echo 'selected'; ?>>16</option>
									<option value="17" <?php if($userBirthdayDay === 17) echo 'selected'; ?>>17</option>
									<option value="18" <?php if($userBirthdayDay === 18) echo 'selected'; ?>>18</option>
									<option value="19" <?php if($userBirthdayDay === 19) echo 'selected'; ?>>19</option>
									<option value="20" <?php if($userBirthdayDay === 20) echo 'selected'; ?>>20</option>
									<option value="21" <?php if($userBirthdayDay === 21) echo 'selected'; ?>>21</option>
									<option value="22" <?php if($userBirthdayDay === 22) echo 'selected'; ?>>22</option>
									<option value="23" <?php if($userBirthdayDay === 23) echo 'selected'; ?>>23</option>
									<option value="24" <?php if($userBirthdayDay === 24) echo 'selected'; ?>>24</option>
									<option value="25" <?php if($userBirthdayDay === 25) echo 'selected'; ?>>25</option>
									<option value="26" <?php if($userBirthdayDay === 26) echo 'selected'; ?>>26</option>
									<option value="27" <?php if($userBirthdayDay === 27) echo 'selected'; ?>>27</option>
									<option value="28" <?php if($userBirthdayDay === 28) echo 'selected'; ?>>28</option>
									<option value="29" <?php if($userBirthdayDay === 29) echo 'selected'; ?>>29</option>
									<option value="30" <?php if($userBirthdayDay === 30) echo 'selected'; ?>>30</option>
									<option value="31" <?php if($userBirthdayDay === 31) echo 'selected'; ?>>31</option>
								</select>
								<select name="birthday-year">
									<option value="0">Year</option>
									<option value="2014" <?php if($userBirthdayYear === 2014) echo 'selected'; ?>>2014</option>
									<option value="2013" <?php if($userBirthdayYear === 2013) echo 'selected'; ?>>2013</option>
									<option value="2012" <?php if($userBirthdayYear === 2012) echo 'selected'; ?>>2012</option>
									<option value="2011" <?php if($userBirthdayYear === 2011) echo 'selected'; ?>>2011</option>
									<option value="2010" <?php if($userBirthdayYear === 2010) echo 'selected'; ?>>2010</option>
									<option value="2009" <?php if($userBirthdayYear === 2009) echo 'selected'; ?>>2009</option>
									<option value="2008" <?php if($userBirthdayYear === 2008) echo 'selected'; ?>>2008</option>
									<option value="2007" <?php if($userBirthdayYear === 2007) echo 'selected'; ?>>2007</option>
									<option value="2006" <?php if($userBirthdayYear === 2006) echo 'selected'; ?>>2006</option>
									<option value="2005" <?php if($userBirthdayYear === 2005) echo 'selected'; ?>>2005</option>
									<option value="2004" <?php if($userBirthdayYear === 2004) echo 'selected'; ?>>2004</option>
									<option value="2003" <?php if($userBirthdayYear === 2003) echo 'selected'; ?>>2003</option>
									<option value="2002" <?php if($userBirthdayYear === 2002) echo 'selected'; ?>>2002</option>
									<option value="2001" <?php if($userBirthdayYear === 2001) echo 'selected'; ?>>2001</option>
									<option value="2000" <?php if($userBirthdayYear === 2000) echo 'selected'; ?>>2000</option>
									<option value="1999" <?php if($userBirthdayYear === 1999) echo 'selected'; ?>>1999</option>
									<option value="1998" <?php if($userBirthdayYear === 1998) echo 'selected'; ?>>1998</option>
									<option value="1997" <?php if($userBirthdayYear === 1997) echo 'selected'; ?>>1997</option>
									<option value="1996" <?php if($userBirthdayYear === 1996) echo 'selected'; ?>>1996</option>
									<option value="1995" <?php if($userBirthdayYear === 1995) echo 'selected'; ?>>1995</option>
									<option value="1994" <?php if($userBirthdayYear === 1994) echo 'selected'; ?>>1994</option>
									<option value="1993" <?php if($userBirthdayYear === 1993) echo 'selected'; ?>>1993</option>
									<option value="1992" <?php if($userBirthdayYear === 1992) echo 'selected'; ?>>1992</option>
									<option value="1991" <?php if($userBirthdayYear === 1991) echo 'selected'; ?>>1991</option>
									<option value="1990" <?php if($userBirthdayYear === 1990) echo 'selected'; ?>>1990</option>
									<option value="1989" <?php if($userBirthdayYear === 1989) echo 'selected'; ?>>1989</option>
									<option value="1988" <?php if($userBirthdayYear === 1988) echo 'selected'; ?>>1988</option>
									<option value="1987" <?php if($userBirthdayYear === 1987) echo 'selected'; ?>>1987</option>
									<option value="1986" <?php if($userBirthdayYear === 1986) echo 'selected'; ?>>1986</option>
									<option value="1985" <?php if($userBirthdayYear === 1985) echo 'selected'; ?>>1985</option>
									<option value="1984" <?php if($userBirthdayYear === 1984) echo 'selected'; ?>>1984</option>
									<option value="1983" <?php if($userBirthdayYear === 1983) echo 'selected'; ?>>1983</option>
									<option value="1982" <?php if($userBirthdayYear === 1982) echo 'selected'; ?>>1982</option>
									<option value="1981" <?php if($userBirthdayYear === 1981) echo 'selected'; ?>>1981</option>
									<option value="1980" <?php if($userBirthdayYear === 1980) echo 'selected'; ?>>1980</option>
									<option value="1979" <?php if($userBirthdayYear === 1979) echo 'selected'; ?>>1979</option>
									<option value="1978" <?php if($userBirthdayYear === 1978) echo 'selected'; ?>>1978</option>
									<option value="1977" <?php if($userBirthdayYear === 1977) echo 'selected'; ?>>1977</option>
									<option value="1976" <?php if($userBirthdayYear === 1976) echo 'selected'; ?>>1976</option>
									<option value="1975" <?php if($userBirthdayYear === 1975) echo 'selected'; ?>>1975</option>
									<option value="1974" <?php if($userBirthdayYear === 1974) echo 'selected'; ?>>1974</option>
									<option value="1973" <?php if($userBirthdayYear === 1973) echo 'selected'; ?>>1973</option>
									<option value="1972" <?php if($userBirthdayYear === 1972) echo 'selected'; ?>>1972</option>
									<option value="1971" <?php if($userBirthdayYear === 1971) echo 'selected'; ?>>1971</option>
									<option value="1970" <?php if($userBirthdayYear === 1970) echo 'selected'; ?>>1970</option>
									<option value="1969" <?php if($userBirthdayYear === 1969) echo 'selected'; ?>>1969</option>
									<option value="1968" <?php if($userBirthdayYear === 1968) echo 'selected'; ?>>1968</option>
									<option value="1967" <?php if($userBirthdayYear === 1967) echo 'selected'; ?>>1967</option>
									<option value="1966" <?php if($userBirthdayYear === 1966) echo 'selected'; ?>>1966</option>
									<option value="1965" <?php if($userBirthdayYear === 1965) echo 'selected'; ?>>1965</option>
									<option value="1964" <?php if($userBirthdayYear === 1964) echo 'selected'; ?>>1964</option>
									<option value="1963" <?php if($userBirthdayYear === 1963) echo 'selected'; ?>>1963</option>
									<option value="1962" <?php if($userBirthdayYear === 1962) echo 'selected'; ?>>1962</option>
									<option value="1961" <?php if($userBirthdayYear === 1961) echo 'selected'; ?>>1961</option>
									<option value="1960" <?php if($userBirthdayYear === 1960) echo 'selected'; ?>>1960</option>
									<option value="1959" <?php if($userBirthdayYear === 1959) echo 'selected'; ?>>1959</option>
									<option value="1958" <?php if($userBirthdayYear === 1958) echo 'selected'; ?>>1958</option>
									<option value="1957" <?php if($userBirthdayYear === 1957) echo 'selected'; ?>>1957</option>
									<option value="1956" <?php if($userBirthdayYear === 1956) echo 'selected'; ?>>1956</option>
									<option value="1955" <?php if($userBirthdayYear === 1955) echo 'selected'; ?>>1955</option>
									<option value="1954" <?php if($userBirthdayYear === 1954) echo 'selected'; ?>>1954</option>
									<option value="1953" <?php if($userBirthdayYear === 1953) echo 'selected'; ?>>1953</option>
									<option value="1952" <?php if($userBirthdayYear === 1952) echo 'selected'; ?>>1952</option>
									<option value="1951" <?php if($userBirthdayYear === 1951) echo 'selected'; ?>>1951</option>
									<option value="1950" <?php if($userBirthdayYear === 1950) echo 'selected'; ?>>1950</option>
									<option value="1949" <?php if($userBirthdayYear === 1949) echo 'selected'; ?>>1949</option>
									<option value="1948" <?php if($userBirthdayYear === 1948) echo 'selected'; ?>>1948</option>
									<option value="1947" <?php if($userBirthdayYear === 1947) echo 'selected'; ?>>1947</option>
									<option value="1946" <?php if($userBirthdayYear === 1946) echo 'selected'; ?>>1946</option>
									<option value="1945" <?php if($userBirthdayYear === 1945) echo 'selected'; ?>>1945</option>
									<option value="1944" <?php if($userBirthdayYear === 1944) echo 'selected'; ?>>1944</option>
									<option value="1943" <?php if($userBirthdayYear === 1943) echo 'selected'; ?>>1943</option>
									<option value="1942" <?php if($userBirthdayYear === 1942) echo 'selected'; ?>>1942</option>
									<option value="1941" <?php if($userBirthdayYear === 1941) echo 'selected'; ?>>1941</option>
									<option value="1940" <?php if($userBirthdayYear === 1940) echo 'selected'; ?>>1940</option>
									<option value="1939" <?php if($userBirthdayYear === 1939) echo 'selected'; ?>>1939</option>
									<option value="1938" <?php if($userBirthdayYear === 1938) echo 'selected'; ?>>1938</option>
									<option value="1937" <?php if($userBirthdayYear === 1937) echo 'selected'; ?>>1937</option>
									<option value="1936" <?php if($userBirthdayYear === 1936) echo 'selected'; ?>>1936</option>
									<option value="1935" <?php if($userBirthdayYear === 1935) echo 'selected'; ?>>1935</option>
									<option value="1934" <?php if($userBirthdayYear === 1934) echo 'selected'; ?>>1934</option>
									<option value="1933" <?php if($userBirthdayYear === 1933) echo 'selected'; ?>>1933</option>
									<option value="1932" <?php if($userBirthdayYear === 1932) echo 'selected'; ?>>1932</option>
									<option value="1931" <?php if($userBirthdayYear === 1931) echo 'selected'; ?>>1931</option>
									<option value="1930" <?php if($userBirthdayYear === 1930) echo 'selected'; ?>>1930</option>
									<option value="1929" <?php if($userBirthdayYear === 1929) echo 'selected'; ?>>1929</option>
									<option value="1928" <?php if($userBirthdayYear === 1928) echo 'selected'; ?>>1928</option>
									<option value="1927" <?php if($userBirthdayYear === 1927) echo 'selected'; ?>>1927</option>
									<option value="1926" <?php if($userBirthdayYear === 1926) echo 'selected'; ?>>1926</option>
									<option value="1925" <?php if($userBirthdayYear === 1925) echo 'selected'; ?>>1925</option>
									<option value="1924" <?php if($userBirthdayYear === 1924) echo 'selected'; ?>>1924</option>
									<option value="1923" <?php if($userBirthdayYear === 1923) echo 'selected'; ?>>1923</option>
									<option value="1922" <?php if($userBirthdayYear === 1922) echo 'selected'; ?>>1922</option>
									<option value="1921" <?php if($userBirthdayYear === 1921) echo 'selected'; ?>>1921</option>
									<option value="1920" <?php if($userBirthdayYear === 1920) echo 'selected'; ?>>1920</option>
									<option value="1919" <?php if($userBirthdayYear === 1919) echo 'selected'; ?>>1919</option>
									<option value="1918" <?php if($userBirthdayYear === 1918) echo 'selected'; ?>>1918</option>
									<option value="1917" <?php if($userBirthdayYear === 1917) echo 'selected'; ?>>1917</option>
									<option value="1916" <?php if($userBirthdayYear === 1916) echo 'selected'; ?>>1916</option>
									<option value="1915" <?php if($userBirthdayYear === 1915) echo 'selected'; ?>>1915</option>
									<option value="1914" <?php if($userBirthdayYear === 1914) echo 'selected'; ?>>1914</option>
									<option value="1913" <?php if($userBirthdayYear === 1913) echo 'selected'; ?>>1913</option>
									<option value="1912" <?php if($userBirthdayYear === 1912) echo 'selected'; ?>>1912</option>
									<option value="1911" <?php if($userBirthdayYear === 1911) echo 'selected'; ?>>1911</option>
									<option value="1910" <?php if($userBirthdayYear === 1910) echo 'selected'; ?>>1910</option>
									<option value="1909" <?php if($userBirthdayYear === 1909) echo 'selected'; ?>>1909</option>
									<option value="1908" <?php if($userBirthdayYear === 1908) echo 'selected'; ?>>1908</option>
									<option value="1907" <?php if($userBirthdayYear === 1907) echo 'selected'; ?>>1907</option>
									<option value="1906" <?php if($userBirthdayYear === 1906) echo 'selected'; ?>>1906</option>
									<option value="1905" <?php if($userBirthdayYear === 1905) echo 'selected'; ?>>1905</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div id="gender">
									<label for="male">Male</label><input id="male" type="radio" name="gender" value="m" <?php if($user->data()->gender === 'm') echo 'checked'; ?>>
									<label for="female">Female</label><input id="female" type="radio" name="gender" value="f" <?php if($user->data()->gender === 'f') echo 'checked'; ?>>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="about-me">About Me:</label>
							</td>
							<td>
								<textarea id="about-me" form="update-form" name="about-me" maxlength="750"><?php echo escape($user->data()->aboutMe); ?></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							</td>
							<td>
								<input type="submit" name="update-profile" value="Save">
							</td>
						</tr>
					</table>
				</form>
				<h3 class="subtitle">Password</h3>
				<form action="" method="post">
					<table>
						<tr>
							<td>
								<label class="textfield" for="current-password">Current Password:</label>
							</td>
							<td>
								<input id="current-password" type="password" name="current-password">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="new-password">New Password:</label>
							</td>
							<td>
								<input id="new-password" type="password" name="new-password">
							</td>
						</tr>
						<tr>
							<td>
								<label class="textfield" for="new-password-confirm">Re-enter New Password:</label>
							</td>
							<td>
								<input id="new-password-confirm" type="password" name="new-password-confirm">
							</td>
						</tr>
						<tr>
							<td>
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							</td>
							<td>
								<input type="submit" name="change-password" value="Save">
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</body>
</html>