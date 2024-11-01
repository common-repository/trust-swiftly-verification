<?php

namespace TrustSwiftly\Exceptions;

use Exception;

/**
 * Exception Class for Configs
 */
class ConfigException extends Exception{

    const MISSING_REQUIRED_CONFIG=0;
    const NULL_OR_EMPTY_CONFIG=1;

    /**
     * @param $message
     * @param int $code
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}