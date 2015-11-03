<?php
/**
 *
 * An implementation on the GCM Interface described here: http://developer.android.com/google/gcm/adv.html
 * Getting Started here: http://developer.android.com/google/gcm/gs.htm
 *
 * Requirements: PHP Version >5.4, php5-curl
 *
 * @author:     Jan Meier <jan@meier.wtf>
 * @date:       03.12.2014
 * @license:    MIT https://github.com/Jan1337z/GCM-via-PHP/blob/master/LICENSE
 * @link:       https://github.com/Jan1337z/GCM-via-PHP
 *
 *
 */

namespace GCM;

class Sender
{
    // API KEY
    private $api_key;
    // recipients
    private $recipients;
    // request timeout (in secs)
    private $timeout = 10;


    // GCM settings
    private $delay_while_idle = false;
    private $collapse_key = "DEFAULT_KEY";
    private $time_to_live = false;
    private $dry_run = false;

    // GCM TARGET URL
    const GCM_URL = 'https://android.googleapis.com/gcm/send';

    /**
     * @link http://developer.android.com/google/gcm/gs.html#access-key
     * @param String $api_key The Google-Cloud-Messaging API-Key
     */
    function __construct($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * method to change the api_key passed to the constructor
     * @link http://developer.android.com/google/gcm/gs.html#access-key
     * @param String $key the GCM Api Key
     */
    function setApiKey($key)
    {
        $this->api_key = $key;
    }

    /**
     * Alias function for @see setRegistrationIds()
     * @link http://developer.android.com/google/gcm/notifications.html#gen-client
     * @link http://developer.android.com/google/gcm/notifications.html#add
     * @param array $recipients an Array of registration-ids from android devices
     */
    function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * Sets the the registration-ids that should receive the GCM-Message
     * @link http://developer.android.com/google/gcm/notifications.html#gen-client
     * @link http://developer.android.com/google/gcm/notifications.html#add
     * @param array $recipients an Array of registration-ids from android devices
     */
    function setRegistrationIds($recipients)
    {
        $this->setRecipients($recipients);
    }

    /**
     * the collapse_key flag plays a role: if there is already a message with the same collapse key (and registration ID)
     * stored and waiting for delivery, the old message will be discarded and the new message will take its place
     * (that is, the old message will be collapsed by the new one). However, if the collapse key is not set, both the
     * new and old messages are stored for future delivery. Collapsible messages are also called send-to-sync messages.
     * @link http://developer.android.com/google/gcm/server.html#params
     * @param String $key collapse_key
     */
    function setCollapseKey($key)
    {
        $this->collapse_key = $key;
    }

    /**
     * This parameter specifies how long (in seconds) the message should be kept on GCM storage if the device is offline.
     * Optional default time-to-live is 4 weeks.
     * @param int $secs seconds in closed set [0, 2419200]
     * @return true if the value got set
     */
    function setTimeToLive($secs)
    {
        if (0 > $secs || $secs > 2419200) {
            throw new \InvalidArgumentException('Parameter $secs should be in set [0,2419200]');
        }
        $this->time_to_live = $secs;
        return true;
    }


    /**
     * Set curl connection Timeout
     * @param int $secs seconds until timeout in set [0,inf)
     * @return true if the value got set
     */
    function setTimeout($secs)
    {
        if (0 > $secs) {
            throw new \InvalidArgumentException('Parameter $secs should be in set [0,inf)');
        }
        $this->timeout = $secs;
        return true;
    }


    /**
     * This parameter allows developers to test a request without actually sending a message.
     * @link http://developer.android.com/google/gcm/server.html#params
     * @param bool $boolean enables/disables dry-run mode
     */
    function setDryrun($boolean)
    {
        $this->dry_run = $boolean;
    }


    /**
     * Sends a Tickle (Send-to-sync message) to the to all recipients
     * @link http://developer.android.com/google/gcm/adv.html#s2s
     * @throws \Exception is there are missing information
     */
    function sendTickle()
    {
        $this->sendMessage("");
    }


    /**
     * Sends message to GCM API-Server
     * @link http://developer.android.com/google/gcm/adv.html#payload
     * @param array $data either empty string or associative-array of information that is sent to the client(s)
     * @return array an array of status per registration_ids/recipiants
     * @throws \Exception if handling the HTTP-Code goes wrong
     */
    function sendMessage($data)
    {

        $s = curl_init();
        // stop echo
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        // set URL
        curl_setopt($s, CURLOPT_URL, $this::GCM_URL);
        // set request mode to POST
        curl_setopt($s, CURLOPT_POST, true);
        // set GCM Headers:
        // Content-Type & Authorization
        curl_setopt($s, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization:key=' . $this->api_key
        ));

        // create Json-String
        $payload = $this->buildJSON($data);

        // sets payload
        curl_setopt($s, CURLOPT_POSTFIELDS, $payload);


        // sets connection timeout
        curl_setopt($s, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        // execute the curl
        $responseBody = curl_exec($s);
        $httpCode = curl_getinfo($s, CURLINFO_HTTP_CODE);
        // close
        curl_close($s);

        if ($this->handleHttpCode($httpCode)) {
            $responseBodyObj = json_decode($responseBody);
            $status = $this->handleSuccessResponse($responseBodyObj->results);
        } else {
            throw new \Exception("Error! Handling the HTTP-Code went wrong.");
        }
        return $status;
    }

    /**
     * @param int $httpResponseCode the http-status-code returned by the Google GCM API-Server
     * @return bool is status  200 - Success ?
     * @throws \Exception if something went wrong (Auth-Error/Internal-Server-Error/Bad-Json)
     */
    static function handleHttpCode($httpResponseCode)
    {
        switch (true) {
            case($httpResponseCode === 200):
                return true;
                break;
            case($httpResponseCode === 400):
                throw new GCMBadResponseCodeException("Error! Response-Code 400: JSON-Request could not be parsed by GCM-Server.");
                break;
            case($httpResponseCode === 401):
                throw new GCMBadResponseCodeException("Error! Response-Code 401: Authenticating error.");
                break;
            case(500 <= $httpResponseCode && $httpResponseCode >= 599):
                throw new GCMBadResponseCodeException("Error! Response-Code {$httpResponseCode}:  Internal error or service temporary unavailable.");
                break;
            default:
                throw new GCMBadResponseCodeException("Error: Internal Server Error on GCM-Server.");
                break;
        }
    }

    /**
     * builds the JSON-Request-String from class-attributes and parameter
     * @param mixed $data is either an empty string or associative-array of information that is sent to the client(s)
     * @return string a stringified JSON-Object containing api-information
     * @throws \Exception if $data is neither an array nor a an empty string OR api_key/recipients is not set
     */
    protected function buildJSON($data)
    {
        // check if basic information are set
        if (empty($this->api_key) or !is_array($this->recipients)) {
            throw new \Exception("Error: api_key or recipients is not set.");
        } elseif (!is_array($data) && !empty($data)) {
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
        if ($this->time_to_live !== false) {
            $request["time_to_live"] = $this->time_to_live;
        }
        return json_encode($request);
    }

    /**
     * @param array $obj
     * @return array an Array of state for each registration_id the message was sent to
     */
    private function handleSuccessResponse($obj)
    {

        $whatToDo = array();

        foreach ($obj as $i => $obj) {
            if (property_exists($obj, 'message_id')) {
                $whatToDo[$i] = DeviceTokenStatus::OK;
            } elseif (property_exists($obj, 'registration_id')) {
                $whatToDo[$i] = DeviceTokenStatus::UPDATE;
            } elseif (property_exists($obj, 'error')) {
                $whatToDo[$i] = DeviceTokenStatus::ERROR;
            } else {
                print("This should not have happened.");
            }
        }
        return $whatToDo;
    }
}
