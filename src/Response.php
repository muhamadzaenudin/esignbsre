<?php

namespace Muhamadzaenudin\Esignbsre;

class Response
{
    public const STATUS_OK = 200;
    public const STATUS_ERROR = 400;
    public const STATUS_TIMEOUT = 408;

    private $status;
    private $message;
    private $data;
    private $errors;

    public function __construct($status = true, $message = '', $data = null, $errors = null)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }

    public static function success($data = null, $message = 'Success', $code = self::STATUS_OK)
    {
        http_response_code($code);
        return new self(true, $message, $data, null);
    }

    public static function error($message = 'Error', $errors = null, $code = self::STATUS_ERROR)
    {
        http_response_code($code);
        return new self(false, $message, null, $errors);
    }

    public function toJson()
    {
        return json_encode([
            'status' => $this->status,
            'code' => http_response_code(),
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors
        ]);
    }
}
