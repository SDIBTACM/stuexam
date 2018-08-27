<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 7/9/16 01:35
 */

namespace Constant\ReqResult;


class Result
{
    public $status;

    public $message;

    public $data = null;

    /**
     * Result constructor.
     * @param $status
     * @param $message
     */
    public function __construct($status = true, $message = "") {
        $this->status = $status;
        $this->message = $message;
    }


    /**
     * @return boolean
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return null
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param null $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    public static function errorResult($message) {
        return new Result(false, $message);
    }

    public static function successResult() {
        return new Result(true);
    }

    public static function successResultWithData($data) {
        $result = new Result();
        $result->setData($data);
        return $result;
    }
}