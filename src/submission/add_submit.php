<?php
//POST: collector_name, tag, edition, quantity, infinite, notes

include '../php/lib.php';

$db = Db_factory::get('sites');
$userBrowser = new UserBrowser();

//validate user
if ($userBrowser->set_available_ids()) {
	
	if (!$userBrowser->ids_are_valid()) {
		throwAway('UserID');
		throwAway('SecretID');
		$redirectURL = '../add.php?error=Your browser identity was invalid.';
		redirect($redirectURL);
	}
} else {
	try {
		$ids = $userBrowser->generate_ids_for_name($_POST['collector_name']);
		$userBrowser->set_ids($ids['user'], $ids['secret']);
		bake('UserID', $ids['user']);
		bake('SecretID', $ids['secret']);
		
	} catch (Exception $e) {
		$redirectURL = '../add.php?error=Name already in use by another browser.';
		redirect($redirectURL);
	}
}

//insert into database
if ($apiResponse = YgoPrices::get_data_by_tag(strtoupper($_POST['tag']))) {
	$time = time();
	$requestQ = "
		INSERT IGNORE INTO ygh_Request (UserID, CardName, CardSet, CardEdition, Quantity, Note, StartTime)
		VALUES (?,?,?,?,?,?,'$time')
	";
	
	$cardName = htmlentities($apiResponse->get_card_name());
	$setName = htmlentities($apiResponse->get_set_name());
	
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
	
	$requestStmt = $db->prepare($requestQ) or die($db->error);
	$userBrowser->userID;
	$requestStmt->bind_param('isssis', $userBrowser->userID, $cardName, $setName, $edition, $quantity, $note);
	$requestStmt->execute() or die($db->error);
	
	if ($requestStmt->insert_id !== 0) {
		//$redirectURL = '../view.php?requestID=' . $requestStmt->insert_id;
		$redirectURL = '../add.php';
		redirect($redirectURL);
	} else {
		$redirectURL = '../add.php?error=You have already made an identical request.';
		redirect($redirectURL);
	}
	
} else {
	$redirectURL = '../add.php?error=Invalid print tag.';
	redirect($redirectURL);
}
?>