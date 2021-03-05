<?php


if (!isset($__UnionReturnInterface__)) {
    $__UnionReturnInterface__ = true;

    /**
     * Class UnionReturnInterface
     * This is an custom class for interacting with JS
     * @author LuoYinsong
     * @package php\CustomPackAndLogger
     */
    class UnionReturnInterface
    {
        public string $returnCode; // 返回代码
        public string $returnString; // 返回代码对应的可读字符串
        public string $showMessage; // 需要返回给浏览器显示的信息
        public string $data; // 返回的数据

        /**
         * UnionReturnInterface constructor.
         */
        public function __construct()
        {
            $this->returnString= '成功';
            $this->returnCode= '200';
            $this->showMessage = '';
            $this->data = '';
        }

        /**
         * @param array $data
         */
        public function setData(array $data): void
        {
            $this->data = json_encode($data,JSON_UNESCAPED_UNICODE);
        }

        /**
         * @param STSAException $e custom exception
         */
        public function boundSTSAException(STSAException $e): void{
            $eArray = $e->toArray();
            $this->returnCode = $eArray["returnCode"];
            $this->returnString = $eArray["returnString"];
            $this->showMessage = $eArray["showMessage"];
        }
    }
}