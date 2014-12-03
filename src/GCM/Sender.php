<?php
/*

	@author: 	Jan Meier
	@eMail:		jan@meier.wtf
	@date:		03.12.2014

	* http://developer.android.com/google/gcm/adv.html
	* apt-get install php5-curl
*/

namespace GCM;

class Sender {
	// GCM TARGT URL
	private $gcm_url = 'https://android.googleapis.com/gcm/send';
	// API KEY
	private $api_key;
	// recipients
	private $recipients;
	// request timeout (in secs)
	private $timeout=10;


	// GCM settings
	private $delay_while_idle = false;
	private $collapse_key = "DEFAULT_KEY";
	private $time_to_live = false;
	private $dry_run = false;

	// "constants"
	public static  $GCM_ERROR	=	2;
	public static  $GCM_UPDATE	=	1;
	public static  $GCM_OK 		=	0;

	function __construct ($api_key) {
		$this->api_key = $api_key;

	}

	function setApiKey($key) {
		$this->api_key = $api_key;
	}

	// set recipients: array!
	function setRecipients($recipients) {
		$this->recipients = $recipients;
	}

	function setRegistrationIds($recipients) {
		$this->setRecipients($recipients);
	}

	// set collapse key
	function setCollapseKey($key) {
		$this->collapse_key = $key;
	}

	// 0 to 2,419,200 seconds
	// 0 => now or never
	function setTimeToLive($secs) {
		if(0 > $secs && $secs <  2419200) {
			throw new \InvalidArgumentException('Parameter $secs should be in range 0 to 2,419,200');
		}
		$this->time_to_live = $secs;
	}

	// request timeout in seconds
	function setTimeout($secs) {
		$this->timeout = $secs;
	}

	// set dry run
	function setDryrun($boolean) {
		$this->dry_run = $boolean;
	}

	// sends-tickle: http://developer.android.com/google/gcm/adv.html#s2s
	function sendTickle() {
		$this->sendMessage();
	}

	// sends message with payload http://developer.android.com/google/gcm/adv.html#payload
	function sendMessage($data) {

		$s = curl_init();
		// stop echo
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		// set URL
		curl_setopt($s,CURLOPT_URL,$this->gcm_url);
		// set request mode to POST
		curl_setopt($s,	CURLOPT_POST, true);
		// set GCM Headers:
		// Content-Type & Authorization
		curl_setopt($s,	CURLOPT_HTTPHEADER, array(
				'Content-Type:application/json',
				'Authorization:key='.$this->api_key
			));

		// create Json-String
		$payload = $this->buildJSON($data);

		// sets payload
		curl_setopt($s,	CURLOPT_POSTFIELDS, $payload);


		// sets connection timeout
		curl_setopt($s, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		// execute the curl
		$responseBody = curl_exec($s);
		$httpCode = curl_getinfo($s,CURLINFO_HTTP_CODE);
		// close
		curl_close($s);

		if($this->handleHttpCode($httpCode)) {
			$responseBodyObj = json_decode($responseBody);
			$status = $this->handleSuccessResponse($responseBodyObj->results);
		} else {
			throw new \Exception("Error! Something went wrong.");
		}

		return $status;
	}

	private static function handleHttpCode($httpResponseCode) {
		switch ($httpResponseCode) {
			case 200:
				return true;
				break;
			case 400:
				throw new \Exception("Error! Response-Code 400: JSON-Request could not be parst by GCM-Server.");
				break;
			case 401:
				throw new \Exception("Error! Response-Code 401: Authenticating error.");
				break;
			default:
				throw new \Exception("Error: Internal Server Error on GCM-Server.");
				break;
		}

		return false;
	}
	private function buildJSON($data) {
		// check if basic informations are set
		if(empty($this->api_key) or !is_array($this->recipients)) {
			throw new \Exception("Error: api_key, recipients or data is not set.");
		} elseif(!is_array($data) && !empty($data)) {
			throw new \InvalidArgumentException("Has to be either an Array or empty");
		}
		// construct request array
		$request = array(
						"collapse_key" => $this->collapse_key,
						"registration_ids" => $this->recipients,
						"delay_while_idle" => $this->delay_while_idle,
						"data" => $data,
						"dry_run" => $this->dry_run
			);
		// if TTL is set by -> added it
		if($this->time_to_live != false) {
			$request["time_to_live"] = $this->time_to_live;
		}
		return json_encode($request);
	}
	private function handleSuccessResponse($obj) {

		$whatToDo = array();

		foreach($obj as $i => $obj) {
			if(property_exists($obj,'message_id')) {
				$whatToDo[$i]	=	Sender::$GCM_OK;
			} elseif(property_exists($obj,'registration_id')) {
				$whatToDo[$i]	=	Sender::$GCM_UPDATE;
			} elseif(property_exists($obj,'error')) {
				$whatToDo[$i]	=	Sender::$GCM_ERROR;
			} else {
				print("This should not have happened.");
			}
		}
		return $whatToDo;
	}
}
