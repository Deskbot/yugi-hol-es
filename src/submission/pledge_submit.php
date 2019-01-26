<?php
//POST: name, amountPledged, requestID
include '../php/lib.php';

$db = Db_factory::get('sites');
$userBrowser = new UserBrowser();

//validate user
if ($userBrowser->set_available_ids()) {
	if (!$userBrowser->ids_are_valid()) {
		throwAway('UserID');
		throwAway('SecretID');
		$redirectURL = '../view.php' . getGETasUrlStr() . '&error=Your browser identity was invalid.';
		redirect($redirectURL);
	}
} else {
	try {
		$ids = $userBrowser->generate_ids_for_name($_POST['collector_name']);
		$userBrowser->set_ids($ids['user'], $ids['secret']);
		bake('UserID', $ids['user']);
		bake('SecretID', $ids['secret']);
		
	} catch (Exception $e) {
		$redirectURL = '../view.php' . getGETasUrlStr() . '&error=Name already in use by another browser.';
		redirect($redirectURL);
	}
}

if (!is_numeric($_POST['amountPledged'])) {var_dump($_POST);
	echo $_POST['amountPledged'];die;
	$redirectURL = '../view.php' . getGETasUrlStr() . '&error=That quantity wasn\'t even a number.';
	redirect($redirectURL);
}

//insert into database
$time = time();

$pledgeQ = "INSERT INTO ygh_Pledge (RequestID, PledgeMakerID, QuantityPledged, Time) VALUES (?,?,?,?)";
$pledgeStmt = $db->prepare($pledgeQ);
$pledgeStmt->bind_param('iiii', $_POST['requestID'], $userBrowser->get_userID(), $_POST['amountPledged'], time());
$pledgeStmt->execute();

$redirectURL = '../view.php' . getGETasUrlStr();
redirect($redirectURL);
?>