<?php
//POST requestID
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

//delete from database
if ($userBrowser->made_request($_POST['requestID'])) {
	$deleteQ = "DELETE FROM ygh_Request WHERE RequestID=? and UserID=?";
	$deleteStmt = $db->prepare($deleteQ) or die($db->error);
	$deleteStmt->bind_param('ii', $_POST['requestID'], $userBrowser->get_userID());
	$deleteStmt->execute() or die($db->error);
	
	//$redirectURL = '../find.php';
	$redirectURL = '../view.php' . getGETasUrlStr();
	redirect($redirectURL);
	
} else {
	$redirectURL = '../index.php?error=You did not make that request, so you can not delete it.';
	redirect($redirectURL);
}
?>