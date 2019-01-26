<?php
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . /* insert working dir here*/);
include ROOT . 'php/constants.php';
include ROOT . 'php/database.php';
include ROOT . 'php/ygoApi.php';
include ROOT . 'php/output.php';

function bake($name, $value) {
	setcookie($name, $value, time() + 315360000, '/');//ten years, in the domain
}
function throwAway($name) {
	setcookie($name, '', time() - 1000);//will expire when tab closes
}

function redirect($url) {
	$newHeader = 'Location: ' . $url;
	header($newHeader);
	die;
}

function randString($length) {
	$characters = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	$numOfCharacters = count($characters);
	
	for ($i = 0; $i < $length; $i++) {
		$randString .= $characters[rand(0, $numOfCharacters)];
	}
	return $randString;
}

function getGETasUrlStr() {
	static $GETurlStr;
	
	if (is_null($GETurlStr)) {
		$url =& $_SERVER['HTTP_REFERER'];
		$firstQMarkPos = strpos($url, '?');
		$GETurlStr = substr($url, $firstQMarkPos, strlen($url) - $firstQMarkPos);
	}
	
	return $GETurlStr;
}
function get_GET_url_encoded_sans_error() {
	$str = getGETasUrlStr();
	$error1Pos = strpos($str, '?error=');
	$error2Pos = strpos($str, '&error=');
	
	if ($error1Pos !== false) {
		$errorPos =& $error1Pos;
		
	} else if ($error2Pos !== false) {
		$errorPos =& $error2Pos;
		
	} else {
		return $str;
	}
	
	$startPos = $errorPos + 1;
	$ampersandPos = strpos($str, '&', $startPos);
	$endPos = $ampersandPos === false ? strlen($str) : $ampersandPos + 1;
	
	return substr($str, 0, $startPos) . substr($str, $endPos, strlen($str) - $endPos);
}

class PigeonHole {
	private $template, $holes, $holeTypePosition, $positionsOfHoles, $splitTempArr;
	
	function __construct($templateString, $insertionSubStringsArr) {
		$this->template = $templateString;
		$this->holes = $insertionSubStringsArr;
		$this->splitTempArr = array();
		$this->holeTypePosition = array();
		$this->positionsOfHoles = array();
		
		$this->compile();
	}
	
	//public functions
	function insert($pigeons) {
		$pigeonsInHoles = '';
		$orderedHoles = array_values($this->positionsOfHoles);
		
		for ($i=0; $i < count($this->splitTempArr) - 1; $i++) {
			$holeType = $orderedHoles[$i];
			if (array_key_exists($holeType, $pigeons)) {
				$pigeon = $pigeons[$holeType];
				$pigeonsInHoles .= $this->splitTempArr[$i] . $pigeon;
			}
		}
		
		$pigeonsInHoles .= $this->splitTempArr[$i];
		
		return $pigeonsInHoles;
	}
	
	//private functions
	private function compile() {
		foreach ($this->holes as $holeType) {
			$this->holeTypePosition[$holeType] = array();
			$currentHole =& $this->holeTypePosition[$holeType];
			
			$lastPos = 0;
			while (($lastPos = strpos($this->template, $holeType, $lastPos)) !== false) {
				$currentHole[] = $lastPos;
				$this->positionsOfHoles[$lastPos] = $holeType;
				$lastPos = $lastPos + strlen($holeType);
			}
		}
		
		ksort($this->positionsOfHoles);
		
		$nextStartPos = 0;
		foreach ($this->positionsOfHoles as $holePosition => $holeType) {
			$this->splitTempArr[] = substr($this->template, $nextStartPos, $holePosition - $nextStartPos);
			$nextStartPos = $holePosition + strlen($holeType);
		}
		$this->splitTempArr[] = substr($this->template, $nextStartPos, strlen($this->template)-1);
	}
}

class UserBrowser {
	private $db;
	public $userID, $secretID, $name;
	
	function __construct() {
		$this->db = Db_factory::get(MAIN_DB);
	}
	
	//private functions
	private function create_id() {
		$idq = "INSERT INTO ygh_User (Name) VALUES (?)";
		$idStmt = $this->db->prepare($idq) or die($this->db->error);
		
		$idStmt->bind_param('s', $name);
		$idStmt->execute() or die($this->db->error);
		
		return $idStmt->insert_id;
	}
	private function ids_are_set() {
		return isset($this->userID) && isset($this->secretID);
	}
	
	//public functions
	function ids_are_valid() {
		if ($this->ids_are_set()) {
			$idsQ = "SELECT Name FROM `ygh_User` WHERE UserID=? AND SecretID=? LIMIT 1";
			$idStmt = $this->db->prepare($idsQ);
			$idStmt->bind_param('is', $this->userID, $this->secretID);
			$idStmt->execute();
			$idStmt->store_result();
			
			return $idStmt->num_rows === 1;
		} else {
			return false;
		}
	}
	
	function generate_ids_for_name($name) {
		$secretIDQ = "SELECT SecretID FROM ygh_User WHERE SecretID=? LIMIT 1";
		
		do {
			$secretID = randString(64);
			
			$secretIDStmt = $this->db->prepare($secretIDQ) or die($this->db->error);
			$secretIDStmt->bind_param('s', $secretID);
			$secretIDStmt->execute() or die($this->db->error);
			$secretIDStmt->store_result();
			
		} while ($secretIDStmt->num_rows === 1);
		
		$nameQ = "INSERT IGNORE INTO ygh_User (SecretID, Name) VALUES (?,?)";
		$nameStmt = $this->db->prepare($nameQ) or die($this->db->error);
		$nameStmt->bind_param('ss', $secretID, $name);
		$nameStmt->execute() or die($this->db->error);
		
		if ($nameStmt->insert_id !== 0) {
			return array('user' => $nameStmt->insert_id, 'secret' => $secretID);
		} else {
			throw new Exception('name is taken');
		}
	}
	
	function made_request($requestID) {
		if (!is_numeric($requestID)) {
			return false;
		}
		$requestQ = "SELECT UserID FROM ygh_Request WHERE requestID=? LIMIT 1";
		$requestStmt = $this->db->prepare($requestQ);
		$requestStmt->bind_param('i', $requestID);
		$requestStmt->execute();
		$requestStmt->bind_result($idOfUser);
		$requestStmt->fetch();
		
		return $idOfUser === $this->userID;
	}
	
	function made_pledge($pledgeID) {
		if (!is_numeric($pledgeID)) {
			return false;
		}
		$requestQ = "SELECT PledgeMakerID FROM ygh_Pledge WHERE PledgeID=? LIMIT 1";
		$requestStmt = $this->db->prepare($requestQ) or die($this->db->error);
		$requestStmt->bind_param('i', $pledgeID);
		$requestStmt->execute() or die($this->db->error);
		$requestStmt->bind_result($idOfUser);
		$requestStmt->fetch();
		
		return $idOfUser === $this->userID;
	}
	
	//getters ands setters
	function get_name_from_db() {
		if (!$this->ids_are_set()) {
			return false;
		}
		
		$nameQ = "SELECT Name FROM ygh_User WHERE UserID=? LIMIT 1";
		
		$nameStmt = $this->db->prepare($nameQ) or die($this->db->error);
		$nameStmt->bind_param('i', $this->userID);
		$nameStmt->execute() or die($this->db->error);
		$nameStmt->bind_result($name);
		$nameStmt->fetch();
		
		return $name;
	}
	
	function set_available_ids() {
		if (isset($_COOKIE['UserID']) && isset($_COOKIE['SecretID'])) {
			$this->set_ids($_COOKIE['UserID'], $_COOKIE['SecretID']);
			
			return true;
		} else {
			return false;
		}
	}
	function set_ids($userID, $secretID) {
		$this->userID = (int) $userID;
		$this->secretID = $secretID;
	}
	
	function get_userID() {
		return $this->userID;
	}
	function get_secretID() {
		return $this->get_secretID;
	}
}
?>
