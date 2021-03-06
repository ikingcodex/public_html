<?php
require_once 'class.db.php';

if(!($user->is_loggedin()))
{
		$user->redirect('login.php');
}
if (isset($_POST["logout"])) {
	if($user->logout()){
		$user->redirect('login.php');
	}
}
if ($user->is_blocked()) {
	$user->redirect('index.php');
}
$uname = $_SESSION['user_session'];

	$select = $database->db->prepare("SELECT * FROM users WHERE username=:uname");
	$select->execute(array(':uname'=>$uname));
	$userRow = $select->fetch(PDO::FETCH_ASSOC);
	$username = $userRow['username'];
	$phone_number = $userRow['phone_number'];
	$account_name = $userRow['account_name'];
	$account_number = $userRow['account_number'];
	$bank_name = $userRow['bank_name'];
	$email = $userRow['email'];
	$cycle = $userRow['number_of_cycles'];

	if(isset($_POST['btn-update'])){

		$username=htmlspecialchars(strip_tags(trim($_POST['username'])));
		$email=htmlspecialchars(strip_tags(trim($_POST['email'])));
		$pnumber=htmlspecialchars(strip_tags(trim($_POST['pnumber'])));
		$bank=htmlspecialchars(strip_tags(trim($_POST['bank'])));
		$acc_number=htmlspecialchars(strip_tags(trim($_POST['accnumber'])));
		$acc_name=htmlspecialchars(strip_tags(trim($_POST['accname'])));
		$oldpassword=htmlspecialchars(strip_tags(trim($_POST['oldpassword'])));
		$newpassword=htmlspecialchars(strip_tags(trim($_POST['newpassword'])));

		 if($username == "") {
				$error = "provide username !";
		 }
		 else if($pnumber == "" ){
			 $error = 'Phone number must not be empty';
		}
		 else if((strlen($pnumber) < 11) || (strlen($pnumber) > 11)){
				$error = 'Phone number must be 11 digits!, i.e 080-00-00-0000';
		 }
		 else if($email == "") {
				$error =  "email field cannot be empty!";
		 }
		 else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$error = 'Please enter a valid email address !';
		 }
		 else if($acc_number == ""){
			 $error = 'Account number cannot be empty!';
		}
		else if((strlen($acc_number) < 10) || (strlen($acc_number) > 10)){
				$error = 'Account number must be 10 digits!';
		}
		else if($acc_name == ""){
				$error = 'Account name cannot!';
		}
		else if(($oldpassword == "") && ($newpassword != "")) {
				$error = "provide old password to be able to change new password !";
		}
		else if(($newpassword != "") && ($oldpassword == "")) {
				$error = "provide old password to be able to change new password !";
		}
		else if(((strlen($oldpassword) < 6) || (strlen($newpassword) < 6)) && (($oldpassword == "") && ($newpassword != ""))){
				$error = "Password must be atleast 6 characters";
		}
		 else{
				try
				{
					 $stmt = $user->db->prepare("SELECT username,email FROM users WHERE username=:uname OR email=:umail");
					 $stmt->execute(array(':uname'=>$username, ':umail'=>$email));
					 $row=$stmt->fetch(PDO::FETCH_ASSOC);

					 $ustmt = $user->db->prepare("SELECT username, email, password FROM users WHERE username=:uname");
					 $ustmt->execute(array(':uname'=>$uname));
					 $urow=$ustmt->fetch(PDO::FETCH_ASSOC);
					  if ($username != $urow['username']) {
						  if($row['username'] == $username) {
								$error = "sorry username already taken !";
						  }
					  }
						elseif ($email != $urow['email']) {
							if($row['email'] == $email) {
	 							$error = "sorry email id already taken !";
	 					  }
						}
					  else{
							if(($oldpassword != "") && ($newpassword != "")){
								if(password_verify($oldpassword, $urow['password'])){
									$password = $newpassword;
								}
								else{
									$error = "Old password incorrect";
								}
							}elseif($oldpassword == "" && $newpassword == ""){
								$password = "";
							}
					 }
			 }
			 catch(PDOException $e)
			 {
					echo $e->getMessage();
			 }
		}
	}
 ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<link rel="icon" type="image/png" href="assets/img/favicon.ico">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>OpenPay Investments</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />


    <!-- Bootstrap core CSS     -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Animation library for notifications   -->
    <link href="assets/css/animate.min.css" rel="stylesheet"/>

    <!--  Light Bootstrap Table core CSS    -->
    <link href="assets/css/light-bootstrap-dashboard.css" rel="stylesheet"/>


    <!--  CSS for Demo Purpose, don't include it in your project     -->
    <link href="assets/css/demo.css" rel="stylesheet" />


    <!--     Fonts and icons     -->
    <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>
    <link href="assets/css/pe-icon-7-stroke.css" rel="stylesheet" />
		<style>
			.logbot{
				background: transparent;
				border: none;
				padding:0px;
				text-align: center;
			}
			#bank{
				height: 40px;
				border-radius: 0px;
				padding: 10px 50px;
				border: 1px solid grey;
				margin-bottom: 10px;
			}
			@media only screen and (max-width: 450px) {
			    #bank{
						width: 80%;
					}
			}

			.number_of_cycles{
				text-align: center;
				font-size: 20px;
				letter-spacing: 3px;
			}
			.error_message{
				font-size: 20px;
				text-align: center;
				padding: 20px;
				background-color: #f23f3f;
				color: white;
				margin: 40px 0px;
			}
		</style>
</head>
<body>
	<div class="wrapper">
	    <div class="sidebar" data-color="<?php if($user->is_admin()){echo'red';}else{ echo 'purple';} ?>" data-image="assets/img/sidebar-5.jpg">

	    <!--   you can change the color of the sidebar using: data-color="blue | azure | green | orange | red | purple" -->


	    	<div class="sidebar-wrapper">
	            <div class="logo">
	                <a class="simple-text">
	                    OpenPay
	                </a>
	            </div>

	            <ul class="nav">
								<li>
										<a href="profile.php">
												<i class="pe-7s-note2"></i>
												<p>Cycle List</p>
										</a>
								</li>
	                <li class="active">
	                    <a href="user.php">
	                        <i class="pe-7s-user"></i>
	                        <p><?php if($user->is_admin()){?>Admin<?php }else{?>User<?php } ?> Profile</p>
	                    </a>
	            </ul>
	    	</div>
	    </div>

	    <div class="main-panel">
			<nav class="navbar navbar-default navbar-fixed">
	            <div class="container-fluid">
	                <div class="navbar-header">
	                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigation-example-2">
	                        <span class="sr-only">Toggle navigation</span>
	                        <span class="icon-bar"></span>
	                        <span class="icon-bar"></span>
	                        <span class="icon-bar"></span>
	                    </button>
	                    <a class="navbar-brand" href="#">Hello, <?php echo $username ;?>.</a>
	                </div>
	                <div class="collapse navbar-collapse">

	                    <ul class="nav navbar-nav navbar-right">
	                        <li>
	                            <a href="">
	                               <p>Community</p>
	                            </a>
	                        <li>
														<a>
															<form action="profile.php" method="post">
																<input type="submit" name="logout" value="Log out" class="logbot">
															</form>
														</a>
	                        </li>
							<li class="separator hidden-lg hidden-md"></li>
	                    </ul>
	                </div>
	            </div>
	        </nav>


	        <div class="content">
							<?php
							if(isset($_POST['btn-update'])){
							 if(isset($error)){ ?>
								 <div class="card error_message">
								<p><?php
								 echo $error;
								 ?></p>
								 </div>
								<?php }else{
									if($user->update($username,$email,$pnumber,$bank,$acc_number,$acc_name,$password)){
										$select = $database->db->prepare("SELECT * FROM users WHERE username=:uname");
										$select->execute(array(':uname'=>$uname));
										$userRow = $select->fetch(PDO::FETCH_ASSOC);
										$username = $userRow['username'];
										$_SESSION['user_session'] = $username ;
										?>
										<div class="card error_message">
											changes saved.
										</div>
										<?php
									}
								}
							}
								 ?>
	            <div class="container-fluid">
	                <div class="row">
	                    <div class="col-md-12" style="margin:auto">
	                        <div class="card">
	                            <div class="header">
	                                <h4 class="title">Edit Profile</h4>
	                            </div>
	                            <div class="content">
	                                <form action="user.php" method="post" name="signform" onsubmit="return signup()">
	                                    <div class="row">
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label>Username</label>
	                                                <input type="text" name="username" id="username" class="form-control" placeholder="enter username" value="<?php echo $username; ?>" required>
	                                            </div>
	                                        </div>
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label>Email</label>
	                                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" value="<?php echo $email; ?>" required>
	                                            </div>
	                                        </div>
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label for="exampleInputEmail1">Phone Number</label>
	                                                <input type="text" name="pnumber" id="pnumber" class="form-control" placeholder="Enter Number" value="<?php echo $phone_number; ?>" required>
	                                            </div>
	                                        </div>
	                                    </div>

	                                    <div class="row">
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label>Account Number</label>
	                                                <input type="text" name="accnumber" id="accnumber" class="form-control" placeholder="Enter Account Number" value="<?php echo $account_number; ?>" required>
	                                            </div>
	                                        </div>
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label>Account Name</label>
	                                                <input type="text" name="accname" id="accname" class="form-control" placeholder="Enter Account Name" value="<?php echo $account_name; ?>" required>
	                                            </div>
	                                        </div>
																					<div class="col-md-4">
																						<div class="form-group">
																							<label>Bank Name</label>
																							<select name="bank" class="cs-select cs-skin-elastic col-md-12 col-sm-12" id="bank">
																		  					<option value="<?php echo $bank_name; ?>"selected><?php echo $bank_name; ?></option>
																		  					<option value="Diamond Bank">Diamond Bank</option>
																		  					<option value="First Bank">First Bank</option>
																		  					<option value="Skye Bank">Skye Bank</option>
																		  					<option value="Eco Bank">Eco Bank</option>
																		            <option value="Keystone Bank">Keystone Bank</option>
																		  					<option value="Guaranty Trust Bank">Guaranty Trust Bank</option>
																		            <option value="Wema Bank">Wema Bank</option>
																		  					<option value="UBA Bank">UBA Bank</option>
																		            <option value="Access Bank">Access Bank</option>
																		            <option value="City Bank">City Bank</option>
																		            <option value="Enterprise Bank">Enterprise Bank</option>
																		            <option value="Fidelity Bank">Fidelity Bank</option>
																		            <option value="First City Monument Bank">First City Monument Bank</option>
																		            <option value="Heritage Bank">Heritage Bank</option>
																		            <option value="Stanbic IBTC Bank">Stanbic IBTC Bank</option>
																		            <option value="Standard Chartered Bank">Standard Chartered Bank</option>
																		            <option value="Union Bank">Union Bank</option>
																		            <option value="Zenith Bank">Zenith Bank</option>
																		  				</select>
																						</div>

																					</div>
	                                    </div>

	                                    <div class="row">
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label>Enter Old Password</label>
	                                                <input type="password" class="form-control" name="oldpassword" placeholder="Enter old password" value="">
	                                            </div>
	                                        </div>
	                                        <div class="col-md-4">
	                                            <div class="form-group">
	                                                <label>Enter New Password</label>
	                                                <input type="password" class="form-control" name="newpassword" placeholder="Enter new password" value="">
	                                            </div>
	                                        </div>
	                                    </div>
																			<?php if(!($user->is_in_cycle())){ ?>
	                                    <button type="submit" class="btn btn-info btn-fill pull-right" name="btn-update">Update Profile</button>
																			<?php } ?>
	                                    <div class="clearfix"></div>
	                                </form>

	                            </div>
	                        </div>
													<div class="number_of_cycles">
														<?php echo $cycle;  ?> Cycles.
													</div>
	                    </div>

	                </div>
	            </div>
	        </div>


	        <footer class="footer">
	            <div class="container-fluid">
	                <nav class="pull-left">
	                    <ul>
												<li>
														<a href="#">
																Privacy
														</a>
												</li>
                        <li>
                            <a href="#">
                                Terms & Condition
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                FAQ
                            </a>
                        </li>
	                    </ul>
	                </nav>
	                <p class="copyright pull-right">
	                    &copy; <script>document.write(new Date().getFullYear())</script> <a href="http://www.openpay.com">OpenPay</a>
	                </p>
	            </div>
	        </footer>

	    </div>
	</div>
</body>

    <!--   Core JS Files   -->
  <script src="assets/js/jquery-1.10.2.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="js/validate.js"></script>

	<!--  Checkbox, Radio & Switch Plugins -->
	<script src="assets/js/bootstrap-checkbox-radio-switch.js"></script>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="assets/js/light-bootstrap-dashboard.js"></script>

</html>
