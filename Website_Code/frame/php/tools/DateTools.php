<?php


if (!isset($__DateTools__)) {
    $__DateTools__ = true;

    require __DIR__ . '/../../../ROOT_PATH.php';
    require ROOT_PATH . '/frame/php/ExceptionAndLogger/STSAException.php';

    /**
     * Class DateTools
     * This class is used to simplify datetime processing
     * @author LuoYinsong
     * @package php\tools
     */
    class DateTools
    {
        private DateTimeImmutable $baseDatetime; // base time
        private array $lastWeekDatetimeList; // all days of last week of base time
        private array $currentWeekDatetimeList; // all days of current week of base time
        private array $nextWeekDatetimeList; // all days of next week of base time

        /**
         * DateTools constructor.
         * @param string $datetime
         * @throws Exception
         */
        public function __construct(string $datetime=''){
            if ($datetime === '') {
                $this->baseDatetime = new DateTimeImmutable('now');
            }
            else {
                try {
                    $this->baseDatetime = new DateTimeImmutable($datetime);
                } catch (Exception $err) {
                    //TODO: write ERROR LOG
                    throw $err;
                }
            }
            $this->_getAllDatetime();
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
        public static function getCurrentDatetime(string $mode='datetime') {
            if ($mode === 'datetime') {
                return new DateTimeImmutable('now');
            }
            if ($mode === 'string') {
                return (new DateTimeImmutable('now'))->format('Y-m-d');
            }
            // TODO: add warning log
            return new DateTimeImmutable('now');
        }

        /**
         * This function generates datetimes of 21 days around $this->BASE_DATETIME
         * @param void
         * @return void
         */
        private function _getAllDatetime(): void {
            $temp = (int)$this->baseDatetime->format('N') + 7;
            $interval = DateInterval::createFromDateString("-{$temp} days");
            $temp_date = $this->baseDatetime->add($interval);

            $interval = DateInterval::createFromDateString('1 days');
            $this->lastWeekDatetimeList = array($temp_date->add($interval));
            for ($i=1;$i<7;$i++){
                $this->lastWeekDatetimeList[] = end($this->lastWeekDatetimeList)->add($interval);
            }

            $this->currentWeekDatetimeList = array(end($this->lastWeekDatetimeList)->add($interval));
            for ($i=1;$i<7;$i++){
                $this->currentWeekDatetimeList[] = end($this->currentWeekDatetimeList)->add($interval);
            }

            $this->nextWeekDatetimeList = array(end($this->currentWeekDatetimeList)->add($interval));
            for ($i=1;$i<7;$i++){
                $this->nextWeekDatetimeList[] = end($this->nextWeekDatetimeList)->add($interval);
            }
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
            // TODO: write WARNING LOG that mode unknown and use default return
            return $this->baseDatetime;
        }

        /**
         * This function return the day of week (of base time)
         * @return int
         */
        public function getDayOfWeek(): int{
            return (int)$this->baseDatetime->format('N');
        }

        /**
         * This function return an array of datetimes of last week.
         * Use $mode can choose from two type: DateTimeImmutable and string
         *
         * WARNING: if $mode given is unknown, will return as default ($mode='datetime')
         * @param string $mode Choose between 'datetime' and 'string' ('datetime' is default).
         * If 'datetime', function will return with type: DateTimeImmutable;
         * If 'string', function will return string type through build-in method: ->format('Y-m-d')
         * @return array
         */
        public function getLastWeek(string $mode='datetime'): array{
            if ($mode === 'datetime') {
                return $this->lastWeekDatetimeList;
            }
            if ($mode === 'string') {
                $r = array();
                foreach ($this->lastWeekDatetimeList as $v ) {
                    /** @var DateTimeImmutable $v */
                    $r[] = $v->format('Y-m-d');
                }
                return $r;
            }
            // TODO: add warning log
            return $this->lastWeekDatetimeList;
        }

        /**
         * This function return an array of datetimes of current week.
         * Use $mode can choose from two type: DateTimeImmutable and string
         *
         * WARNING: if $mode given is unknown, will return as default ($mode='datetime')
         * @param string $mode Choose between 'datetime' and 'string' ('datetime' is default).
         * If 'datetime', function will return with type: DateTimeImmutable;
         * If 'string', function will return string type through build-in method: ->format('Y-m-d')
         * @return array
         */
        public function getCurrentWeek(string $mode='datetime'): array{
            if ($mode === 'datetime') {
                return $this->currentWeekDatetimeList;
            }
            if ($mode === 'string') {
                $r = array();
                foreach ($this->currentWeekDatetimeList as $v ) {
                    /** @var DateTimeImmutable $v */
                    $r[] = $v->format('Y-m-d');
                }
                return $r;
            }
            // TODO: add warning log
            return $this->currentWeekDatetimeList;
        }

        /**
         * This function return an array of datetimes of next week.
         * Use $mode can choose from two type: DateTimeImmutable and string
         *
         * WARNING: if $mode given is unknown, will return as default ($mode='datetime')
         * @param string $mode Choose between 'datetime' and 'string' ('datetime' is default).
         * If 'datetime', function will return with type: DateTimeImmutable;
         * If 'string', function will return string type through build-in method: ->format('Y-m-d')
         * @return array
         */
        public function getNextWeek(string $mode='datetime'): array{
            if ($mode === 'datetime') {
                return $this->nextWeekDatetimeList;
            }
            if ($mode === 'string') {
                $r = array();
                foreach ($this->nextWeekDatetimeList as $v ) {
                    /** @var DateTimeImmutable $v */
                    $r[] = $v->format('Y-m-d');
                }
                return $r;
            }
            // TODO: add warning log
            return $this->nextWeekDatetimeList;
        }
    }
}
