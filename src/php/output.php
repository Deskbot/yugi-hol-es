<?php
function getSingleRequest($requestID) {
	$db = Db_factory::get(MAIN_DB);
	
	$requestQ = "
		SELECT
			R.RequestID AS RequestID,
			ygh_User.Name AS Name,
			R.CardName AS CardName,
			R.CardSet AS CardSet,
			R.CardEdition AS CardEdition,
			R.Quantity AS TotalWanted,
			R.StartTime AS StartTime,
			R.EndTime AS EndTime,
			SUM(P.QuantityPledged) AS TotalPledged,
			SUM(P.QuantityFulfilled) AS TotalFulfilled
		FROM ygh_Request R
		
		LEFT JOIN (
			(SELECT RequestID, QuantityFulfilled, QuantityPledged FROM ygh_Pledge) AS P
		)
		ON R.RequestID = P.RequestID
		
		LEFT JOIN ygh_User
		ON R.UserID = ygh_User.UserID
		WHERE R.RequestID=?
		GROUP BY R.RequestID
		LIMIT 1
	";
	
	$requestStmt = $db->prepare($requestQ) or die($db->error);
	$requestStmt->bind_param('i', $requestID);
	$requestStmt->execute() or die($db->error);
	$requestResult = new Db_result($requestStmt);
	
	$rowHTML = file_get_contents(ROOT . 'html/request_row.html');
	
	$requestRow = $requestResult->fetch_assoc();
	
	$dateFormat = 'jS F \'y';
	$startDate = date($dateFormat, $requestRow['StartTime']);
	$endDateFormatted = is_null($requestRow['EndTime']) ? '' : 'Finished on: ' . date($dateFormat, $requestRow['EndTime']);
	$timeFormatted = "Started on: $startDate. $endDateFormatted.";
	
	switch ($requestRow['CardEdition']) {
		case '1':
			$edition = '1st';
			break;
		case 'u':
			$edition = 'Unlimited';
			break;
		case 'o':
			$edition = 'Other';
			break;
		case 'a':
			$edition = 'Any';
			break;
	}
	
	$replacementArr = array(
		'[id]'             => $requestRow['RequestID'],
		'[time]'           => $timeFormatted,
		'[collector_name]' => $requestRow['Name'],
		'[card_name]'      => $requestRow['CardName'],
		'[set]'            => $requestRow['CardSet'],
		'[edition]'        => $edition,
		'[fulfilled]'      => is_null($requestRow['TotalFulfilled']) ? 0 : $requestRow['TotalFulfilled'],
		'[pledged]'        => is_null($requestRow['TotalPledged']) ? 0 : $requestRow['TotalPledged'],
		'[wanted]'         => is_null($requestRow['TotalWanted']) ? '&infin;' : $requestRow['TotalWanted']
	);
	
	foreach ($replacementArr as $key => $replacement) {
		$rowHTML = str_replace($key, $replacement, $rowHTML);
	}
	
	return $rowHTML;
}

function getCurrentRequests($completed_param, $sortBy_param, $order, $startAt_param) {
	$db = Db_factory::get(MAIN_DB);
	
	$conditionSQL = '';
	
	switch ($completed_param) {
		case 'pledges':
			$conditionSQL .= 'and TotalPledged >= R.Quantity';
			break;
		case 'fulfilled':
			$conditionSQL .= 'and TotalFulfilled >= R.Quantity';
			break;
		default:
			$conditionSQL .= '';
	}
	
	switch ($sortBy_param) {
		case 'end_time':
			$sortBy = 'R.EndTime';
			break;
		case 'start_time':
		default:
			$sortBy = 'R.StartTime';
	}
	
	switch ($order) {
		case 'ASC':
			break;
		case 'DESC':
			break;
		default:
			$order = 'DESC';
	}
	
	$requestQ = "
		SELECT
			R.RequestID AS RequestID,
			U.Name AS Name,
			R.CardName AS CardName,
			R.CardSet AS CardSet,
			R.CardEdition AS CardEdition,
			R.Quantity AS TotalWanted,
			R.StartTime AS StartTime,
			R.EndTime AS EndTime,
			SUM(P.QuantityPledged) AS TotalPledged,
			SUM(P.QuantityFulfilled) AS TotalFulfilled
		FROM ygh_Request R
		
		LEFT JOIN ygh_Pledge P
		ON R.RequestID = P.RequestID
		
		LEFT JOIN ygh_User U
		ON R.UserID = U.UserID
		
		WHERE R.EndTime IS NULL $conditionSQL
		GROUP BY R.RequestID
		HAVING R.Quantity IS NULL OR SUM(P.QuantityFulfilled) IS NULL OR SUM(P.QuantityFulfilled) < R.Quantity
		ORDER BY $sortBy $order
		LIMIT 20 OFFSET ?
	";
	
	$requestStmt = $db->prepare($requestQ) or die($db->error);
	$requestStmt->bind_param('i', is_numeric($startAt_param) ? $startAt_param : '');
	$requestStmt->execute() or die($db->error);
	$requestResult = new Db_result($requestStmt);
	
	$rowHTML = file_get_contents(ROOT . 'html/request_row.html');
	$rowRepArr = array('[id]','[collector_name]','[card_name]','[set]','[edition]','[fulfilled]','[pledged]','[wanted]','[time]');
	$rowTemplate = new PigeonHole($rowHTML, $rowRepArr);
	
	$outputRows = '';
	
	while ($requestRow = $requestResult->fetch_assoc()) {
		$dateFormat = 'jS F \'y';
		$startDate = date($dateFormat, $requestRow['StartTime']);
		$endDateFormatted = is_null($requestRow['EndTime']) ? '' : 'Finished on: ' . date($dateFormat, $requestRow['EndTime']) . '.';
		$timeFormatted = "Started on: $startDate. $endDateFormatted";
		
		switch ($requestRow['CardEdition']) {
			case '1':
				$edition = '1st';
				break;
			case 'u':
				$edition = 'Unlimited';
				break;
			case 'o':
				$edition = 'Other';
				break;
			case 'a':
				$edition = 'Any';
				break;
		}
		
		$replacementArr = array(
			'[id]'             => $requestRow['RequestID'],
			'[time]'           => $timeFormatted,
			'[collector_name]' => $requestRow['Name'],
			'[card_name]'      => $requestRow['CardName'],
			'[set]'            => $requestRow['CardSet'],
			'[edition]'        => $edition,
			'[fulfilled]'      => is_null($requestRow['TotalFulfilled']) ? 0 : $requestRow['TotalFulfilled'],
			'[pledged]'        => is_null($requestRow['TotalPledged']) ? 0 : $requestRow['TotalPledged'],
			'[wanted]'         => is_null($requestRow['TotalWanted']) ? '&infin;' : $requestRow['TotalWanted'],
		);
		
		$outputRows .= $rowTemplate->insert($replacementArr);
	}
	
	return $outputRows;
}

function getViewControls($requestID) {
	$db = Db_factory::get(MAIN_DB);
	$controlsHTML = file_get_contents(ROOT . 'html/view_controls.html');
	
	$requestQ = "SELECT CardEdition, Quantity, Note FROM ygh_Request WHERE RequestID=?";
	$requestStmt = $db->prepare($requestQ) or die($db->error);
	$requestStmt->bind_param('i', $requestID);
	$requestStmt->execute() or die($db->error);
	$requestResult = new Db_result($requestStmt);
	$reqRow = $requestResult->fetch_assoc();
	
	$selected['1'] = '';
	$selected['u'] = '';
	$selected['o'] = '';
	$selected['a'] = '';
	
	$selected[$reqRow['CardEdition']] = 'selected';
	
	$replacementArr = array(
		'[selected_1]' => $selected['1'],
		'[selected_u]' => $selected['u'],
		'[selected_o]' => $selected['o'],
		'[selected_a]' => $selected['a'],
		'[checked]'    => is_null($reqRow['Quantity']) ? 'checked' : '',
		'[note]'       => $reqRow['note'],
		'[requestID]'  => htmlentities($requestID)
	);
	
	foreach ($replacementArr as $key => $replacement) {
		$controlsHTML = str_replace($key, $replacement, $controlsHTML);
	}
	
	return $controlsHTML;
}

function getRequestPledges($requestID, $sortBy_param, $order, $startAt_param) {
	$db = Db_factory::get(MAIN_DB);
	
	$conditionSQL = '';
	
	switch ($sortBy_param) {
		case 'name':
			$sortBy = 'Name';
			break;
		case 'fulfilled':
			$sortBy = 'QuantityFulfilled';
			break;
		case 'pledged':
			$sortBy = 'QuantityPledged';
			break;
		case 'time':
		default:
			$sortBy = 'Time';
	}
	
	switch ($order) {
		case 'ASC':
			break;
		case 'DESC':
			break;
		default:
			$order = 'DESC';
	}
	
	$pledgeQ = "
		SELECT 
			U.Name AS Name,
			P.PledgeID AS PledgeID,
			P.QuantityPledged AS QuantityPledged,
			P.QuantityFulfilled AS QuantityFulfilled,
			P.Time AS Time
		FROM ygh_Pledge P
		
		JOIN ygh_User U
		ON P.PledgeMakerID = U.UserID
		
		WHERE P.RequestID=? $conditionSQL
		ORDER BY $sortBy $order
		LIMIT 20 OFFSET ?
	";
	
	$requestStmt = $db->prepare($pledgeQ) or die($db->error);
	$requestStmt->bind_param('ii', $requestID, is_numeric($startAt_param) ? $startAt_param : '');
	$requestStmt->execute() or die($db->error);
	$requestStmt->store_result();
	
	if ($requestStmt->num_rows === 0) {
		$emptyRow = '<td>None...</td><td></td><td></td><td></td><td></td>';
		return $emptyRow;
	}
	
	$requestResult = new Db_result($requestStmt);
	
	$rowHTML = file_get_contents(ROOT . 'html/pledge_row.html');
	$rowRepArr = array('[pledger_name]','[date]','[controls]','[fulfilled]','[pledged]');
	$rowTemplate = new PigeonHole($rowHTML, $rowRepArr);
	
	$controlsHTML = file_get_contents(ROOT . 'html/pledge_controls.html');
	$controlsRepArr = array('[fulfilled]','[pledgeID]');
	$controlsTemplate = new PigeonHole($controlsHTML, $controlsRepArr);
	
	$outputRows = '';
	
	while ($reqRow = $requestResult->fetch_assoc()) {
		$dateFormat = "jS F 'y";
		$dateFormatted = date($dateFormat, $reqRow['Time']);
		
		$controlsReplacementArr = array(
			'[fulfilled]' => $reqRow['QuantityFulfilled'],
			'[pledgeID]'  => $reqRow['PledgeID']
		);
		
		$rowReplacementArr = array(
			'[pledger_name]' => $reqRow['Name'],
			'[date]'         => $dateFormatted,
			'[controls]'     => $controlsTemplate->insert($controlsReplacementArr),
			'[fulfilled]'    => $reqRow['QuantityFulfilled'],
			'[pledged]'      => $reqRow['QuantityPledged']
		);
		
		$outputRows .= $rowTemplate->insert($rowReplacementArr);
	}
	
	return $outputRows;
}
?>