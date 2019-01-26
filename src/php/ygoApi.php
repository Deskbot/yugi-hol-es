<?php
//api class
class YgoPrices {
	const setDataUrl = 'http://yugiohprices.com/api/set_data/';
	const cardSetsUrl = 'http://yugiohprices.com/api/card_sets';
	const tagDataUrl = 'http://yugiohprices.com/api/price_for_print_tag/';

	//static functions
	static private function callUnsuccessful($response) {
		return $response->status !== 'success';
	}
	
	static private function makeApiCall($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		$response_raw = curl_exec($ch);
		curl_close($ch);
		
		try {
			$response = json_decode($response_raw);
		} catch (Exception $e) {
			echo "YgoPrices::makeApiCall(), Can't decode json: \n";
			echo json_last_error();
			var_dump($response);
			die;
		}
		
		if (self::callUnsuccessful($response)) {
			$error_message = 'Commands out of sync; you can\'t run this command now';
			return false;
		}
		echo $response_raw;
		return $response;
		
		/*
		try {
			if (self::callUnsuccessful($response)) {
				$error_message = 'Commands out of sync; you can\'t run this command now';
				throw new Exception($error_message);
			}
		} catch (Exception $e) {
			var_dump($response);
			die;
		}*/
	}
	
	//public functions
	public static function getAllSetNames() {
		return self::makeApiCall(self::cardSetsUrl);
	}
	
	public static function getSetDataObj($setName, $setLocation) {
		$url = self::setDataUrl . $setName;
		$response = self::makeApiCall($url);
		return new YgoPricesSetResponse(clone $response, $setLocation);
	}
	
	public static function get_data_by_tag($tag) {
		$url = self::tagDataUrl . $tag;
		$response = self::makeApiCall($url);
		if ($response) {
			return new YgoPricesTagResponse(clone $response);
		} else {
			return false;
		}
	}
}

//response class
class YgoPricesSetResponse {
	private $info, $location;
	const TW_LOCATION = 'TW';	//TCG-wide
	const NA_LOCATION = 'NA';	//North America
	const EU_LOCATION = 'EU';	//Europe
	const TW_TAG = 'EN';		//TCG-wide
	const NA_TAG = '';			//North America
	const EU_TAG = 'E';			//Europe
	
	function __construct($response, $location) {
		$this->info = $response->data;
		$this->location = $location;
		
		//remove anything from the wrong location
		if ($location === self::TW_LOCATION) {
			$isRightLocation = function($tag) {
				return self::printTagIsTcgWide($tag);
			};
		} else if ($location === self::NA_LOCATION) {
			$isRightLocation = function($tag) {
				return self::printTagIsNorthAmerica($tag);
			};
		} else if ($location === self::EU_LOCATION) {
			$isRightLocation = function($tag) {
				return self::printTagIsEurope($tag);
			};
		} else {
			incorrectForm('location');
		}
		
		foreach ($this->info->cards as $key => $card) {
			$printTag = $card->numbers[0]->print_tag;	//each of the numbers items will be from the same location
			
			if (!$isRightLocation($printTag)) {
				unset($this->info->cards[$key]);
			}
		}
		
		$this->info->cards = array_values($this->info->cards);
	}
	
	//static functions
	static function printTagIsTcgWide($printTag) {
		$dashPos = strpos($printTag, '-');
		$stringForLang = substr($printTag, $dashPos + 1, 2);	//2 characters long
		return $stringForLang === self::TW_TAG;
	}
	static function printTagIsNorthAmerica($printTag) {
		$dashPos = strpos($printTag, '-');
		$stringForLang = substr($printTag, $dashPos + 1, 2);	//2 characters long
		return $stringForLang !== self::TW_TAG && $printTag[$dashPos + 1] !== self::EU_TAG;
	}
	static function printTagIsEurope($printTag) {
		$dashPos = strpos($printTag, '-');
		$printTag[$dashPos + 1];
		return $printTag[$dashPos + 1] === self::EU_TAG && $printTag[$dashPos + 2] !== 'N';//E but not EN
	}
	static function GURtoPGD($rarityName) {
		if ($rarityName === 'Gold Rare') {
			return 'Premium Gold Rare';
		} else {
			return $rarityName;
		}
	}
	static function giveSearchableString($string) {
		$charPattern = '/[^\x20-\x7E]|\s|\(|\)|\.|-|"|^|&|%|£|$|\*|#|\'|@|=|>|</i';	//the g for global is implied
		$badCharsRemoved = preg_replace($charPattern, '', $string);
		
		$commonWordsPattern = '/(The\s)|(A\s)|(\sthe\s)|(\sof\s)|(\sa\s)/';				//the g for global is implied
		$badCharsRemoved = preg_replace($commonWordsPattern, '', $badCharsRemoved);
		
		return strtolower($badCharsRemoved);
	}
	
	//public functions
	public function exists() {
		return count($this->info->cards) !== 0;
	}
	
	public function getAcronym() {
		$printTag = $this->info->cards[0]->numbers[0]->print_tag;
		$dashPos = strpos($printTag,'-');
		return substr($printTag, 0, $dashPos);				//returns string from beginning to before the dash
	}
	
	public function getName() {
		return $this->info->cards[0]->numbers[0]->name;		//first number of first card in the list
	}
	
	public function getCardsFormatted() {
		$name_key = 'name';
		$name_searchable_key = 'nameSearchable';
		$rarity_key = 'rarity';
		
		$cards_formated = array();
		
		//determine rarity fixing function
		if (strpos($this->getName(), 'Premium Gold') !== false) {	//will probably return 0
			$fixRarity = function($rarity) {
				return self::GURtoPGD($rarity);
			};
		} else {
			$fixRarity = function($rarity) {
				return $rarity;
			};
		}
		
		//make the formatted array
		foreach ($this->info->cards as $card) {						//loop through each card in the set
			$name = $card->name;
			$searchable = self::giveSearchableString($card->name);
			
			foreach ($card->numbers as $cardRendition) {	//loop through each rendition of a card within the set
				$rarity = $cardRendition->rarity;
				
				$cards_formated[] = array(				//add card info to formatted array
					$name_key => $name,
					$name_searchable_key => $searchable,
					$rarity_key => $fixRarity($rarity)
				);
			}
		}
		
		return $cards_formated;
	}
}

class YgoPricesTagResponse {
	private $info;
	
	function __construct($response) {
		$this->info = $response->data;
	}
	
	//public functions
	function get_card_name() {
		return $this->info->name;
	}
	function get_set_name() {
		return $this->info->price_data->name;
	}
}
?>