<?php
//POST pledgeID, newQuantity

include '../php/lib.php';

$db = Db_factory::get('sites');
$userBrowser = new UserBrowser();

//validate user
if ($userBrowser->set_available_ids()) {//cookies are set
	if (!$userBrowser->ids_are_valid()) {//cookies are invalid
		throwAway('UserID');
		throwAway('SecretID');
		$redirectURL = '../index.php?error=Your browser identity was invalid.';
		redirect($redirectURL);
	}
} else {
	$redirectURL = '../index.php?error=You have not added a card and therefore can not be validated.';
	redirect($redirectURL);
}

if ($userBrowser->made_pledge($_POST['pledgeID'])) {
	if (!is_numeric($_POST['newQuantity']) || $_POST['newQuantity'] < 0) {
		$redirectURL = '../index.php?error=Invalid new number given.';
		redirect($redirectURL);
	}
	
	//get current quantity
	$quantityQ = "SELECT QuantityPledged FROM ygh_Pledge WHERE PledgeID=?";
	$quantityStmt = $db->prepare($quantityQ) or die($db->error);
	$quantityStmt->bind_param('i', $_POST['pledgeID']);
	$quantityStmt->execute() or die($db->error);
	$quantityStmt->store_result();
	$quantityStmt->bind_result($maxQuantity);
	$quantityStmt->fetch();
	
	$newQuantity = $_POST['newQuantity'] > $maxQuantity ? $maxQuantity : $_POST['newQuantity'];
	
	//update database
	$updateQ = "UPDATE ygh_Pledge SET QuantityFulfilled=? WHERE PledgeID=? and PledgeMakerID=?";
	$updateStmt = $db->prepare($updateQ) or die($db->error);
	$updateStmt->bind_param('iii', $newQuantity, $_POST['pledgeID'], $userBrowser->get_userID());
	$updateStmt->execute() or die($db->error);
	
	$redirectURL = '../view.php' . get_GET_url_encoded_sans_error();
	redirect($redirectURL);
	
} else {
	$redirectURL = '../index.php?error=You did not make that request, so you can not delete it.';
	redirect($redirectURL);
}
?>