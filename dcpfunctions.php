<?php
	/* 
		****
		Everything gets escaped before it reaches any of these functions. Which is better.
		Uses mysqli as the database driver since mysql_ is deprecated and very prone to SQL injection attacks.
		I should've used parameterized SQL queries.
		For some reason github doesn't seem to indent the code after I copy it from notepad++ to here.
		****
	*/
	
	/* Includes and other important stuff*/
	include('db.php');
	include('includes/Auth.php');
	callMaxDefinitions();
	
	/* Functions */	
	function checkDonation($username) {
		$query3 = "SELECT id, user, donated, givendps, transactionid, ip, checkout, package FROM donations where user = '$username' && checkout = '0' && canceled = '0' order by id DESC LIMIT 1"; //gets some values
		$result3 = gs_query($query3);
		$row_donate = mysqli_fetch_assoc($result3);
  	
		$rowdonate_id = $row_donate['id'];
		$rowdonate_checkout = $row_donate['checkout'];
		$rowdonate_canceled = $row_donate['canceled'];
		$rowdonate_donated = $row_donate['donated'];
		$rowdonate_givendps = $row_donate['givendps'];
		$rowdonate_packagenr = $row_donate['package'];
		$num_donrows = mysqli_num_rows($result3);
		
		$MAX_PACKAGES = $GLOBALS["MAX_PACKAGES"];
		//$identification = $row_donate[0];
  		if($don_disabled == 1) {
  			echo "WARNING: Donations are disabled!<br>";
		}
  		if($num_donrows > 0) {
  			echo "Our system found out that you still have a pending donation package:<br>"; 
			$packdescription = getPackageInformation($rowdonate_packagenr);
			echo "$packdescription<br>";
			echo "<input type='button' value='Cancel It!' name='donatebtn' id='donatebtn' class='donatebtn' onclick='window.top.window.paycanceldon(event, $rowdonate_id, $rowdonate_donated, 1, 0);' style='float: right'>";
			echo "<input type='button' value='Pay It!' name='donatebtn' id='donatebtn' class='donatebtn' onclick='window.top.window.paycanceldon(event, $rowdonate_id, $rowdonate_packagenr, 2, 0);' style='float: right'>";
  		} else {
			echo '<p style="text-align: left">&nbsp;Donation: <select size="1" onchange="window.top.window.showdonationtip(event,2,this.value);" name="charslot" class="charslot" id="charslot">';
			echo '<option value="-1" >Cancel Donation</option>';
			echo "<option value='-2' selected>Resume</option>";
			echo '</select>';
			echo "<div id='donationbox' class='donationbox'>";
    		echo '<p style="text-align: left">&nbsp;Package: <select size="1" onchange="window.top.window.showdonationtip(event,3,this.value);" name="charslot" class="charslot" id="package">';
			for ($i = 1; $i <= $MAX_PACKAGES; $i++) {
				echo "<option value='$i'>".getPackageInformation($i)."</option>";
			}
			echo '</select>';
			echo '<p style="text-align: left">&nbsp;</p>';
    		echo "</div>";
    		echo '<p style="text-align: left">';
    		echo "<input type='button' value='Next' name='donatebtn' id='donatebtn' class='donatebtn' onclick='window.top.window.donatereq(event, 2, 0);' style='float: right'>";
    		echo '</p>';
		}
	}
	function isPackage($pack_nr) {
		if($pack_nr < 5) {
			return 1;
		} else {
			return 0;
		}
	}
	function callMaxDefinitions() {
		/* Globals  */
		$GLOBALS["MAX_UPGRADE_BUSINESS"] = 3;
		$GLOBALS["MAX_UPGRADE_HOUSES"] = 5;
		$GLOBALS["MAX_UPGRADE_CARS"] = 10;
		$GLOBALS["MAX_PACKAGES"] = 8;
	}
	function processPackage($username, $packagenumber) {
		if($don_disabled == 1) {
  			echo "WARNING: Donations are disabled!<br>";
		}
		
		if(!is_numeric ($packagenumber)) {
			echo "<script>parent.showdonationerror(2, 0);</script>";
			die();
		} 
		$packagevalue = getPackageValue($packagenumber);
		$packagedetails = getPackageInformation($packagenumber);
		$packageDPS = getPackageDPS($packagenumber);
		
		$ip = getenv("REMOTE_ADDR");
		if($packagenumber < 0) {
			die("Donation cancelled");
		}
		
		if(!isPackage($packagenumber)) { //It's not a package, but an upgrade
			processUpgrade($username, $packagenumber);
			die(); //Kill the script so it doesn't continue
		}
		
  		$query3 = "SELECT id, user, auth_id FROM donations where user = '$username' && checkout = '0' && canceled = '0' order by id DESC LIMIT 1"; //gets some values
 		$result3 = gs_query($query3) or die("Query failed with error: ".mysqli_error());
		$rowid = mysqli_fetch_assoc($result3);
		$identification = $rowid["auth_id"];
  		$num_rows_donate = mysqli_num_rows($result3);
  		
  		if($num_rows_donate == 0) {
  			$querycreatedonation = "INSERT INTO `donations` (user, donated, givendps, ip, checkout, canceled, package) values ('".$username."', '".$packagevalue."', '".$packageDPS."', '".$ip."', '0', '0', '".$packagenumber."')";
			if( !gs_query( $querycreatedonation ) ) { //If the query fails
				$error = "MySQL error ".mysqli_errno().": ".mysqli_error()."\n<br>When executing:<br>\n$querycreatedonation\n<br>";
				echo "$error<br>";
				die(); //we stop the php script
			} else {
				$getlastid = lastinsertid("id", "donations");
				$identification = decoct($getlastid)*900;
				$updateQuery = "UPDATE `donations` SET auth_id = '$identification' where id = $getlastid";
				gs_query($updateQuery);
			}
  		}
  		
		$identification = getAuthID($username);
		
  		$successreturn = "http://www.inglewoodrp.com/ucp/success.php?idnumber=$identification&&user=$username";
  		
  		$query3 = "SELECT * FROM accounts where username = '".$username."'"; //gets some values
		$result3 = gs_query($query3);
		$row3 = mysqli_fetch_assoc($result3);
		//site admin stuff
  		if($row3['siteadmin'] > 0) {
  			echo "Developer message: $successreturn<br>";
  		}
  		
  		echo "<b>Purchase Details:</b><p>";
  		echo "You are about to purchase:<br>";
		echo "$packagedetails<br>";
  		echo "Continue to payment<p>" ;
  		echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		echo '<input type="hidden" name="cmd" value="_donations">';
		echo '<input type="hidden" name="business" value="removed">';
		echo '<input type="hidden" name="lc" value="US">';
		echo '<input type="hidden" name="item_name" value="West Coast Role Play">';
		echo "<input type='hidden' name='amount' value='$packagevalue'>";
		echo '<input type="hidden" name="currency_code" value="USD">';
		echo '<input type="hidden" name="no_note" value="0">';
		echo '<input type="hidden" name="cn" value="Please, wait to be redirected after donating.">';
		echo '<input type="hidden" name="no_shipping" value="1">';
		echo '<input type="hidden" name="rm" value="2">'; //1 = GET 2 = Post. Default was GET (1)
		echo "<input type='hidden' name='cancel_return' value='http://inglewoodrp.com/ucp/donatorcp.php'>";
		echo "<input type='hidden' name='return' value='$successreturn'>";
		echo '<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted">';
		echo '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" onclick="parent.displaywarning();">';
		echo '<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">';
		echo '</form>';

  		echo "<input type='button' value='Back' name='donatebtn' id='donatebtn' class='donatebtn' onclick='window.top.window.donatereq(event, 3, 0);' style='float: right'>";
	}
	function getAuthID($user) {
		$query3 = "SELECT id, user, auth_id FROM donations where user = '$user' && checkout = '0' && canceled = '0' order by id DESC LIMIT 1"; //gets some values
 		$result3 = gs_query($query3) or die("Query failed with error: ".mysqli_error());
		$rowid = mysqli_fetch_assoc($result3);
		$identification = $rowid["auth_id"];
		return $identification;
	}
	function lastinsertid($id, $tablename) {
		$querylastid = "select LAST_INSERT_ID($id) as '$id' from `$tablename` group by $id DESC limit 1";
		$result = gs_query($querylastid);
		$rowid = mysqli_fetch_assoc($result);
		$last_id = $rowid["$id"];
		return $last_id;
	}
	function cancelDonation($donationid) {
		//echo "ID: $donate_id<br>";
		if($don_disabled == 1) {
			echo "WARNING: Donations are disabled!<br>";
		}
		$querydel = "UPDATE `donations` set canceled = '1' where id = $donationid"; //Delete Query E.G. Update Query
		gs_query($querydel);	
 	
		if(!gs_query($querydel)) {
			$error = "MySQL error ".mysqli_errno().": ".mysqli_error()."\n<br>When executing:<br>\n$querydel\n<br>";
			echo "$error<br>";
		}
		echo "Donation Cancelled!<br>";
		echo "<input type='button' value='Back' name='donatebtn' id='donatebtn' class='donatebtn' onclick='window.top.window.donatereq(event, 3, 0);' style='float: right'>";
		echo "<script>parent.showdonationerror(3, 0);</script>";
	}
	function getPackageInformation($packnumber) {
		switch ($packnumber) {
		case 1:
			$description = "Package 1: ".getPackageDPS($packnumber)." Donator Points, VIP Status ($".getPackageValue($packnumber).")";
			break;
		case 2:
			$description = "Package 2: ".getPackageDPS($packnumber)." Donator Points, VIP Status ($".getPackageValue($packnumber).")";
			break;
		case 3:
			$description = "Package 3: ".getPackageDPS($packnumber)." Donator Points, Trusted Donator Status ($".getPackageValue($packnumber).")";
			break;
		case 4:
			$description = "Package 4: ".getPackageDPS($packnumber)." Donator Points, Trusted Donator Status ($".getPackageValue($packnumber).")";
			break;
		case 5:
			$description = "+1 Car Slot, ".getUpgradeCostDPS($packnumber)." DP's each (10 max)";
			break;
		case 6:
			$description = "+1 House Slot, ".getUpgradeCostDPS($packnumber)." DP's each (5 max)";
			break;
		case 7:
			$description = "+1 Business Slot, ".getUpgradeCostDPS($packnumber)." DP's each (3 max)";
			break;
		case 8:
			$description = "Contact an admin in-game if you want Mapping.";
			break;
		default: 
			$description = "Error<br>";
			break;
		}
		return $description;
	}
	function getPackageDPS($packnumber) {
		switch ($packnumber) {
		case 1:
			$value = "50";
			break;
		case 2:
			$value = "120";
			break;
		case 3:
			$value = "450";
			break;
		case 4:
			$value = "800";
			break;
		default: 
			//Error (something went wrong)
			$value = "0";
			break;
		}
		return $value;
	}
	function getUpgradeCostDPS($packnumber) {
		switch ($packnumber) {
		case 5:
			$value = "50";
			break;
		case 6:
			$value = "100";
			break;
		case 7:
			$value = "250";
			break;
		default: 
			//Error (something went wrong)
			$value = "0";
			break;
		}
		return $value;
	}
	function getPackageValue($packnumber) {
		switch ($packnumber) {
		case 1:
			$value = "5";
			break;
		case 2:
			$value = "10";
			break;
		case 3:
			$value = "35";
			break;
		case 4:
			$value = "50";
			break;
		case 5:
			$value = "5";
			break;
		case 6:
			$value = "10";
			break;
		case 7:
			$value = "25";
			break;
		default: 
			//Error (something went wrong)
			$value = "0";
			break;
		}
		return $value;
	}
	function verifyCheckout($user_name, $tx_nr, $url_authnr) {
		if(empty($tx_nr)) {
			//displayDonError(3);
			$tx_nr = "N/A";
		}
		if(!is_numeric($url_authnr)) {
			die("Error!<br>");
		}
		if(notAssignedYet($url_authnr)) {
			$query3 = "SELECT id, checkout, package, auth_id FROM `donations` where auth_id = '$url_authnr' && checkout = '0' && canceled = '0' ORDER BY ID DESC LIMIT 1"; //gets some values
			$result3 = gs_query($query3);
			$row_donate = mysqli_fetch_assoc($result3);
			$rowdonate_checkout = $row_donate['checkout'];
			$rowdonate_id = $row_donate['id'];
			$rowdonate_packagenr = $row_donate['package'];
			$auth_id = $row_donate['auth_id'];
			assignPackage($user_name, $rowdonate_packagenr, $rowdonate_id, $tx_nr, $url_authnr, $auth_id);
		} else {
			displayDonError(1);
		}
	}
	function notAssignedYet($donationid) {
		$query3 = "SELECT id, checkout, package, auth_id FROM `donations` where auth_id = '$url_authnr' && checkout = '0' && canceled = '0' ORDER BY ID DESC LIMIT 1"; //gets some values
		$result3 = gs_query($query3);
		$num_donrows = mysqli_num_rows($result3);
		if($num_donrows > 0) {
			return 1;
		} else {
			return 0;
		}
	}
	function postedValueHasInfo($postedvalue) {
		if(strlen($postedvalue) > 0) {
			return 1;
		} else {
			return 0;
		}
	}
	function assignPackage($username, $package_number, $donation_id, $txnumber, $urlauthid, $authnr) {
		switch ($urlauthid) {
			case "$authnr":
				$totaldps = getPackageDPS($package_number);
				adjustDPS($username, $totaldps, 0, "+");
				$actualdps = getAccountDPS($username);
				$viprank = getPackageDonateRank($package_number);
				setDonateRank($username, $viprank);
				setCheckOut($donation_id, $txnumber);
				displaySuccessDonMSG(1, $package_number, $actualdps);
				break;
			default:
				//Error (something went wrong)
				if($donation_id != 0) {
					$queryupdate = "UPDATE `donations` SET checkout = -1 WHERE id = $donation_id";
					gs_query($queryupdate);
					echo "URL: $urlauthid Auth ID: $authnr<br>";
				}
				echo "AUTHNR: '$urlauthid', AUTH ID:'$authnr'<br>";
				displayDonError(2);
				break;
		}
	}
	function returnuid($id, $column, $username, $tablename) {
		$queryuid = "select $id from `$tablename` where $column = '$username'";
		$result = gs_query($queryuid);
		$rowuid = mysqli_fetch_assoc($result);
		$uid_return = $rowuid["$id"];
		return $uid_return;
	}
	function processUpgrade($account_username, $package_num) {
		$acc_id = returnuid('id', 'username', $account_username, 'accounts');
		$querychars = "SELECT username, id FROM `characters` WHERE accountid = $acc_id";
		$resultinfochars = gs_query($querychars);
		
		echo "Select the character you wish to upgrade:<br>";
		echo "<div id='displaycharResults' class='displaycharResults'>";
		
		echo "<select size='1' onchange='window.top.window.doUpgrade($package_num);' name='selectchar' class='selectchar' id='selectchar'>";
		echo "<option value='-1' selected>Select your character</option>";
		while ($charsearch= mysqli_fetch_array($resultinfochars)) {
			$charsearch_username = $charsearch["username"];
			$charsearch_id = $charsearch["id"];
			echo "<option value='$charsearch_id'>$charsearch_username</option>";
		}
		echo '</select>';
		echo "</div>";
	}
	function assignUpgrade($username, $package_number, $characterid) {
		//I was going to use this for something..
		onAssignUpgrade($username, $package_number, $characterid);
	}
	function onAssignUpgrade($user_name, $row_packagenr, $character_id) {
		$querytotal = "SELECT username, maxcars, maxhouses, maxbusinesses FROM `characters` where id = '$character_id'"; //gets some values
		$resulttotal = gs_query($querytotal);
		//Rows
		$row_total = mysqli_fetch_assoc($resulttotal);
		$actual_max_cars = $row_total['maxcars'];
		$actual_max_houses = $row_total['maxhouses'];
		$actual_max_businesses = $row_total['maxbusinesses'];
		$actual_donate_points = getAccountDPS($user_name); //Gets the donate points.
		$upgradeCost = getUpgradeCostDPS($row_packagenr);
		$amount = 1; //I prepared something in the DB already, but it's not and will not be used for now...
		$MAX_UPGRADE_CARS = $GLOBALS["MAX_UPGRADE_CARS"];
		$MAX_UPGRADE_HOUSES = $GLOBALS["MAX_UPGRADE_HOUSES"];
		$MAX_UPGRADE_BUSINESS = $GLOBALS["MAX_UPGRADE_BUSINESS"];
		echo "<script>console.log('Max Cars: $actual_max_cars, Max Houses: $actual_max_houses, Max Businesses: $actual_max_businesses,MAX CARS: $MAX_UPGRADE_CARS, MAX Houses: $MAX_UPGRADE_HOUSES, MAX Businesses: $MAX_UPGRADE_BUSINESS');</script>";
		if($actual_donate_points < $upgradeCost) {
			die("Sorry, but you do not have enough DP's for this upgrade, you require $upgradeCost at least.");
		}
		switch ($row_packagenr) {
		case 5:
			if($actual_max_cars < $MAX_UPGRADE_CARS) {
				$set_to = $actual_max_cars+$amount;
				$updatequery = "UPDATE `characters` SET maxcars = '$set_to' WHERE id = '$character_id'";	
			} else {
				displayDonError(4);
			}
			break;
		case 6:
			if($actual_max_houses < $MAX_UPGRADE_HOUSES) {
				$set_to = $actual_max_houses+$amount;
				$updatequery = "UPDATE `characters` SET maxhouses = '$set_to' WHERE id = '$character_id'";
			} else {
				displayDonError(4);
			}
			break;
		case 7:
			if($actual_max_businesses < $MAX_UPGRADE_BUSINESS) {
				$set_to = $actual_max_businesses+$amount;
				$updatequery = "UPDATE `characters` SET maxbusinesses = '$set_to' WHERE id = '$character_id'";
			} else {
				displayDonError(4);
			}
			break;
		case 8:
			//Mapping
			getPackageInformation(8);
			break;
		default: 
			//Error (something went wrong)
			die("Error");
			break;
		}
		if($row_packagenr != 8) {
			echo 'Thank you very much!<br>';
			echo "You have been assigned: <br>";
			$pack_info = getPackageInformation($row_packagenr);
			echo "$pack_info<br>";
			gs_query($updatequery); //Update a query from the switch
			adjustDPS($user_name, $upgradeCost, 0, "-");
		}
	}
	function getAccountDPS($username) {
		$querytotal = "SELECT donatepoints FROM `accounts` where username = '$username'"; //gets some values
		$resulttotal = gs_query($querytotal);
		//Rows
		$row_total = mysqli_fetch_assoc($resulttotal);
		$actual_dps = $row_total['donatepoints'];
		return $actual_dps;
	}
	function adjustDPS($username, $amount, $reset_dps, $operator) { //operator can be: -, +, *, /
		$actualdps = getAccountDPS($username);
		//echo "Called: adjustDPS($username, $amount, $reset_dps, $operator);<br>";
		$actualdps = intval($actualdps);
		$amount = intval($amount);
		switch($operator) {
		case '+' :
			$set_to = $actualdps + $amount;
			break;
		case '-' :
			$set_to = $actualdps - $amount;
			break;
		case '*' :
			$set_to = $actualdps * $amount;
			break;
		case '/' :
			$set_to = $actualdps / $amount;
			break;
		default: 
			//Error (something went wrong)
			die("Error on adjustDPS(). The coder may have specified a parameter wrong.");
			break;
		}
		//
		switch ($reset_dps) { //reset can be used to either set the donate points to 0 or to any other value
		case 0: //Doesn't reset
				$updatequery = "UPDATE `accounts` SET donatepoints = '$set_to' WHERE username = '$username'";
			break;
		case 1: //Resets to some other value
				$updatequery = "UPDATE `accounts` SET donatepoints = '$amount' WHERE username = '$username'";
			break;
		default: 
			//Error (something went wrong)
			die("Error on adjustDPS(). The coder may have specified a parameter wrong.");
			break;
		}
		gs_query($updatequery); //Update a query from the switch
	}
	function getPackageDonateRank($pack_nr) {
		switch ($pack_nr) {
			case 1:
				$vipstatus = 2;
				break;
			case 2:
				$vipstatus = 2;
				break;
			case 3:
				$vipstatus = 4;
				break;
			case 4:
				$vipstatus = 4;
				break;
			default: 
				//Error (something went wrong)
				die("Error");
				break;
		}
		return $vipstatus;
	}
	function setDonateRank($user_name, $d_rank) {
		$queryrank = "SELECT donaterank FROM `accounts` where Username = '".$user_name."'"; //gets some values
		$resultrank = gs_query($queryrank);
		$row_rank = mysqli_fetch_assoc($resultrank);
		$donate_rank = $row_rank['donaterank'];
		if($donate_rank != 4) {
			$query2 = "UPDATE `accounts` SET donaterank = '".$d_rank."' WHERE Username = '".$user_name."'";	
			gs_query($query2);
		}
	}
	function setCheckOut($donationid, $tx_number) {
		$querydon = "UPDATE `donations` SET checkout = '1' WHERE id = '".$donationid."'";
		$resultdon = gs_query($querydon);
		if(!empty($tx_number)) {
			$querytxnumber = "UPDATE `donations` SET transactionid = '".$tx_number."' WHERE id = '".$donationid."'";	
			gs_query($querytxnumber);
		}
	}
	function displayDonError($number) {
		switch ($number) {
			case 1:
				echo "It seems that this donation has been processed already.<br>";
				echo 'Taking you back to the User Control Panel.<p>';
				echo 'If the browser does not redirect you click <a href="main.php">here</a>'; 
				echo '<META HTTP-EQUIV="Refresh" Content="5; URL=main.php">';
				die();
				break;
			case 2:
				echo "The donation couldn't be processed due to an error.<br>";
				echo 'Taking you back to the User Control Panel.<p>';
				echo 'If the browser does not redirect you click <a href="main.php">here</a>'; 
				echo '<META HTTP-EQUIV="Refresh" Content="5; URL=main.php">';
				die();
				break;
			case 3:
				echo "You didn't donate anything.<br>";
				echo 'Taking you back to the User Control Panel.<p>';
				echo 'If the browser does not redirect you click <a href="main.php">here</a>'; 
				echo "Error 0<br>";
				echo '<META HTTP-EQUIV="Refresh" Content="5; URL=main.php">';
				die();
				break;
			case 4:
				echo "You have maxed this out already.<br>";
				echo '<META HTTP-EQUIV="Refresh" Content="5; URL=main.php">';
				die();
				break;
			default: 
				//Error (something went wrong)
				die("Error");
				break;
		}
	}
	function displaySuccessDonMSG($number, $pack_nr, $dps) {
		$pack_info = getPackageInformation($pack_nr);
		switch ($number) {
			case 1:
				echo 'Thank you very much for your donation!<br>';
				echo "You have been assigned: <br>";
				echo "$pack_info<br>";
				echo "Your total donate points are: $dps<br>";
				echo 'Taking you back to the User Control Panel.<p>';
				echo 'If the browser does not redirect you click <a href="main.php">here</a>'; 
				echo '<META HTTP-EQUIV="Refresh" Content="5; URL=main.php">';
				die();
				break;
			default: 
				//Error (something went wrong)
				die("Error");
				break;
		}
	}
	function checkDCPStatus() {
		$querydondisabled = "SELECT donationsdisabled FROM siteoptions"; //gets some values
		$resdondisabled = gs_query($querydondisabled);
		$row_dondisabled = mysqli_fetch_assoc($resdondisabled);
  	
		$don_disabled = $row_dondisabled['donationsdisabled'];

		if($don_disabled == 1 && $row4['siteadmin'] < 1) {
			echo "It seems that the donations are disabled for maintenance purposes, please stand by..<br>";
			die();
		}
	}
?>
