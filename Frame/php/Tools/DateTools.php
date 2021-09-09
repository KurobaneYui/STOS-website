<?php


if (!isset($__DateTools__)) {
    $__DateTools__ = true;

    require_once __DIR__ . '/../../../ROOT_PATH.php';
    require_once ROOT_PATH . '/Frame/php/CustomPackAndLogger/STSAException.php';
    require_once ROOT_PATH . "/Frame/php/CustomPackAndLogger/STSA_log.php";

    /**
     * Class DateTools
     * This class is used to simplify datetime processing
     * @author LuoYinsong
     * @package php\Tools
     */
    class DateTools
    {
        private DateTimeImmutable $baseDatetime; // base time
        private int $currentWeekNum; // day of week of base time
        private string $currentWeekString; // week full name of base time
        private int $currentYearNum; // year num of base time
        private int $currentMonth; // month of year of base time
        private int $currentDayNum; // day of month of base time
        private STSA_log $logger;

        /**
         * DateTools constructor.
         * @param string $datetime
         * @throws Exception
         */
        public function __construct(string $datetime=''){
            $this->logger = new STSA_log();
            if ($datetime === '') {
                $this->baseDatetime = new DateTimeImmutable('now');
            }
            else {
                try {
                    $this->baseDatetime = new DateTimeImmutable($datetime);
                } catch (Exception $err) {
                    $this->logger->add_log(__FILE__.':'.__LINE__, "Init DateTools, input datetime is illegal datetime: {$datetime}", "Error");
                    throw $err;
                }
            }
            $this->_getAllInfo();
        }

        /**
         * This function generates infos of $this->BASE_DATETIME
         * @param void
         * @return void
         */
        private function _getAllInfo(): void {
            $this->currentWeekNum = (int)$this->baseDatetime->format('N');
            $this->currentWeekString = $this->baseDatetime->format("l");
            $this->currentMonth = (int)$this->baseDatetime->format("n");
            $this->currentDayNum = (int)$this->baseDatetime->format("j");
            $this->currentYearNum = (int)$this->baseDatetime->format('Y');
        }

        /**
         * This function return current time in one of two types depended on $mode
         *
         * WARNING: if $mode given is unknown, will return as default ($mode='datetime')
         * @param string $mode Choose between 'datetime' and 'string' ('datetime' is default).
         * If 'datetime', function will return with type: DateTimeImmutable;
         * If 'string', function will return string type through build-in method: ->format('Y-m-d')
         * @return DateTimeImmutable|string
         */
        public static function getCurrentDatetime(string $mode='datetime'): DateTimeImmutable|string{
            if ($mode === 'datetime') {
                return new DateTimeImmutable('now');
            }
            if ($mode === 'string') {
                return (new DateTimeImmutable('now'))->format('Y-m-d');
            }
            if ($mode === 'database') {
                return (new DateTimeImmutable('now'))->format(('Y-m-d H:i:s'));
            }
            (new STSA_log())->add_log(__FILE__.':'.__LINE__, "DateTools get current datetime, input mode is not in accept list and use default mode (datetime), input mode is {{$mode}}", "Warning");
            return new DateTimeImmutable('now');
        }

        /**
         * This function add days to given datetime and return it
         *
         * @param DateTimeImmutable|DateTime $datetime Give datetime.
         * @param int $days Set days to add. Can input negative integer.
         * @return DateTimeImmutable|DateTime
         */
        public static function addDayToDatetime(DateTimeImmutable|DateTime $datetime,int $days): DateTimeImmutable|DateTime{
            $interval = DateInterval::createFromDateString("{$days} days");
            return $datetime->add($interval);
        }

        /**
         * This function add days to base datetime
         *
         * @param int $days Set days to add. Can input negative integer.
         * @return void
         */
        public function addDay(int $days): void{
            $interval = DateInterval::createFromDateString("{$days} days");
            $this->baseDatetime = $this->baseDatetime->add($interval);
            $this->_getAllInfo();
        }

        /**
         * This function return base time in one of two types depended on $mode
         *
         * WARNING: if $mode given is unknown, will return as default ($mode='datetime')
         * @param string $mode Choose between 'datetime' and 'string' ('datetime' is default).
         * If 'datetime', function will return with type: DateTimeImmutable;
         * If 'string', function will return string type through build-in method: ->format('Y-m-d')
         * @return DateTimeImmutable|string
         */
        public function getBaseDatetime(string $mode='datetime') {
            if ($mode === 'datetime') {
                return $this->baseDatetime;
            }
            if ($mode === 'string') {
                return $this->baseDatetime->format('Y-m-d');
            }
            if ($mode === 'database') {
                return (new DateTimeImmutable('now'))->format(('Y-m-d H:i:s'));
            }
            $this->logger->add_log(__FILE__.':'.__LINE__, "DateTools get current datetime, input mode is not in accept list and use default mode (datetime), input mode is {{$mode}}", "Warning");
            return $this->baseDatetime;
        }

        /**
         * This function return the info of basetime
         * @return array
         */
        public function getInfo(): array{
            return [
                'datetime'=>$this->baseDatetime,
                'year'=>$this->currentYearNum,
                'month'=>$this->currentMonth,
                'week'=>$this->currentWeekNum,
                'day'=>$this->currentDayNum,
                'weekName'=>$this->currentWeekString];
        }
    }
}
