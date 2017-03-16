<?php
//POST requestID, infinite, edition, quantity, note
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

if ($userBrowser->made_request($_POST['requestID'])) {
	//validation
	switch ($_POST['edition']) {
		case '1':
			$edition = '1';
			break;
		case 'u':
			$edition = 'u';
			break;
		case 'o':
			$edition = 'o';
			break;
		case 'a':
		default:
			$edition = 'a';
			break;
	}
	$quantity = ($_POST['infinite'] || !is_numeric($_POST['quantity'])) ? null : $_POST['quantity'];
	$note = htmlentities($_POST['note']);
	
	//update database
	$updateQ = "UPDATE ygh_Request SET CardEdition=?, Quantity=?, Note=? WHERE RequestID=? and UserID=?";
	$updateStmt = $db->prepare($updateQ) or die($db->error);
	$updateStmt->bind_param('sisii', $edition, $quantity, $note, $_POST['requestID'], $userBrowser->get_userID());
	$updateStmt->execute() or die($db->error);
	
	//$redirectURL = '../find.php';
	$redirectURL = '../view.php' . getGETasUrlStr();
	redirect($redirectURL);
	
} else {
	$redirectURL = '../index.php?error=You did not make that request, so you can not delete it.';
	redirect($redirectURL);
}
?>
