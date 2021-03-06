<?php

if (!class_exists('MyException')) {
    class MyException extends Exception
    {
        public $error_code;
        public $error_info;

        public function __construct(string $error_code, string $error_info, $message = '', $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->error_info = $error_info;
            $this->error_code = $error_code;
        }

        public function info_out(): array {
            return array($this->error_code,$this->error_info);
        }
    }
}