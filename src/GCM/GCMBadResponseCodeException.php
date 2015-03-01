<?php
/**
 * Created by PhpStorm.
 * User: meier
 * Date: 01.03.15
 * Time: 15:58
 */

namespace GCM;


class GCMBadResponseCodeException extends \Exception{
    function __construct($message, $code=0) {
        parent::__construct($message,$code);
    }
    function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
} 