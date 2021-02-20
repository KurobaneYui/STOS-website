<?php


if (!isset($__STSAException__)) {
    $__STSAException__ = true;

    /**
     * Class STSAException
     * This is an custom sub-class of Exception
     * @author LuoYinsong
     * @package php\ExceptionAndLogger
     */
    class STSAException extends Exception
    {
        private string $showMessage; // 需要返回给浏览器显示的信息

        /**
         * Set $showMessage attribute
         * @param string $additionalMessage
         */
        public function setShowMessage(string $additionalMessage): void{
            $this->showMessage = $additionalMessage;
        }

        public function __toString() {
            return 'Error Code: ' . $this->code . '\nError Message: ' . $this->message . '\nAdditional Message: ' . $this->showMessage;
        }
    }
}