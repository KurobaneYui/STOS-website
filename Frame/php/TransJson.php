<?php
/*
 * 错误代码的含义
 *   11：因为参数类型不匹配，无法调用函数
 *   12：因为参数数值范围或数量不匹配，无法调用函数
 *   13：试图调用一个不存在的函数或功能
 *   14：无权调用
 *   10：未知的调用错误
 *   ——
 *   21：函数执行过程中遇到类型错误
 *   22：函数在执行过程中碰到非预期的值
 *   23：函数读/写了一个不存在的位置或不存在的文件
 *   24：函数碰到权限不足的问题
 *   20：函数执行中碰到未知问题
 *   ——
 *   3x：函数维护，暂不提供功能
 *   ——
 *   00：函数正常执行，但反馈了不合理的输入，需用户修改
 * */
if (!class_exists('TransJson')) {
    class TransJson
    {
        public $status = false;
        public $error_info = '';
        public $error_code = '00';
        public $results = '{}';

        /**
         * TransJson constructor.
         * @param bool $status
         * @param string $error_info
         * @param string $error_code
         * @param string $results
         */
        public function __construct(bool $status, string $error_code, string $error_info, string $results = '')
        {
            $this->status = $status;
            $this->error_info = $error_info;
            $this->error_code = $error_code;
            $this->results = $results;
        }

        // public function: return json-type string of all attributes of class
        /**
         * @return string
         */
        public function encode2json(): string {
            return json_encode(array('status'=>$this->status,'error_info'=>$this->error_info,'error_code'=>$this->error_code,'results'=>$this->results,),JSON_UNESCAPED_UNICODE);
        }
    }
}