<?php


use JetBrains\PhpStorm\Pure;

if (!isset($__STSAException__)) {
    $__STSAException__ = true;

    /**
     * Class STSAException
     * This is an custom sub-class of Exception
     * @author LuoYinsong
     * @package php\CustomPackAndLogger
     */
    class STSAException extends Exception
    {
        public string $errorCode; // 返回代码
        public string $errorString; // 返回代码对应的可读字符串
        public string $showMessage; // 需要返回给浏览器显示的信息

        /**
         * STSAException constructor.
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         * @param string $show
         */
        #[Pure] public function __construct(string $message = "", int $code = 0, Throwable $previous = null, string $show =""){
            parent::__construct($message, $code, $previous);
            $this->errorCode= (string)$code;
            $this->errorString = $message;
            $this->showMessage = $show;
        }

        /**
         * @return string
         */
        public function __toString(): string{
            return 'Return Code: ' . $this->errorCode . '\nReturn Message: ' . $this->errorString . '\nAdditional Message: ' . $this->showMessage;
        }

        /**
         * @return array
         */
        public function toArray(): array{
            return ["returnCode"=>$this->errorCode,"returnString"=>$this->errorString,"showMessage"=>$this->showMessage];
        }

        public function setFromSTSAException(STSAException $ori){
            $this->errorCode = $ori->errorCode;
            $this->errorString = $ori->errorString;
            $this->showMessage = $ori->showMessage;
        }
    }
}
