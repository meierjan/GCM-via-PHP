<?php
/**
 * Created by PhpStorm.
 * User: meier
 * Date: 01.03.15
 * Time: 16:06
 */

namespace GCM;

/**
 *
 */
class DeviceTokenStatus {
    /**
     * i.e. device not registers / wrong registration
     */
    const ERROR = 2;

    /**
     * device id needs to be updated
     */
    const UPDATE = 1;

    /**
     * nothing to worry about - everything went well
     */
    const OK = 0;
} 