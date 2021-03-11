<?php


if (!isset($__ProgramHandleInterface__)) {
    $__ProgramHandleInterface__ = true;

    require_once __DIR__."/../../../ROOT_PATH.php";
    require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSAException.php";
    require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/UnionReturnInterface.php";
    require_once ROOT_PATH . "/Frame/php/Tools/Authorization.php";
// TODO:require log file

    /**
     * Class ProgramHandleInterface
     * This class is used to run program code of website
     * @author LuoYinsong
     * @package php\ProgramHandle
     */
    class ProgramHandleInterface
    {
        private string $root_dir = ROOT_PATH;

        private string $programName;
        private array $programArgv;
        private array $programReturns;
        private int $programReturnCode;

        /**
         * ProgramHandleInterface constructor.
         * @param string $programName executable program name
         * @param array $programArgv argv to program including file name to execute
         * @param bool $relative2webroot if the first argv have relative path to web root
         * @throws STSAException
         */
        public function __construct(string $programName, array $programArgv, bool $relative2webroot=false) {
            $programList = ['python'];
            if(in_array($programName, $programList, true)) {
                $this->programName = $programName;
                $this->programArgv = $programArgv;
                if ($relative2webroot) {
                    $this->programArgv[0] = $this->root_dir.$this->programArgv[0];
                }
            } else {
                throw new STSAException('指定的程序不存在',417);
            }
        }

        /**
         * @param array $programArgv argv to program including file name to execute
         * @param bool $relative2webroot if the first argv have relative path to web root
         */
        public function setProgramArgv(array $programArgv, bool $relative2webroot=false): void{
            $this->programArgv = $programArgv;
            if ($relative2webroot) {
                $this->programArgv[0] = $this->root_dir.$this->programArgv[0];
            }
        }

        /**
         * @return array
         * @throws STSAException
         */
        public function runCode(): array{
            $argvString = implode(' ',$this->programArgv);
            if (!str_starts_with($argvString, "{$this->root_dir}/Program/{$this->programName}/")) {
                throw new STSAException("可执行程序非指定文件夹",417);
            }
            exec("{$this->programName} {$argvString}",$this->programReturns,$this->programReturnCode);
            if ($this->programReturnCode!==0) {
                $returnString = implode('\n',$this->programReturns);
                throw new STSAException("程序运行中错误: {$returnString}",417);
            }
            return $this->programReturns;
        }
    }
}