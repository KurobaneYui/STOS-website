<?php


if (!class_exists('DateTools')){
    class DateTools
    {
        private $IS_CORRECT = false; // datetime is correctly created
        private $BASED_DATETIME; // base time
        private $DAY_OF_WEEK_OF_BASED_DATETIME; // the day of the week of base time
        private $LAST_WEEK_DATETIME_LIST; // all days of last week of base time
        private $CURRENT_WEEK_DATETIME_LIST; // all days of current week of base time
        private $NEXT_WEEK_DATETIME_LIST; // all days of next week of base time

        /**
         * DateTools constructor.
         * @param string $datetime
         * @throws Exception
         */
        public function __construct(string $datetime = '' ){
            if ($datetime === '') {
                $this->BASED_DATETIME = new DateTimeImmutable('now');
                $this->IS_CORRECT = true;
            }
            else{
                try {
                    $this->BASED_DATETIME = new DateTimeImmutable($datetime);
                    $this->IS_CORRECT = true;
                } catch (Exception $e) {
                    $this->IS_CORRECT = false;
                }
            }
            if ($this->IS_CORRECT){
                $this->GET_ALL_DATETIME();
            }
        }

        public function __destruct(){
        }

        private function GET_ALL_DATETIME(): void {
            $this->DAY_OF_WEEK_OF_BASED_DATETIME = (int)$this->BASED_DATETIME->format('N');

            $temp = $this->DAY_OF_WEEK_OF_BASED_DATETIME + 7;
            $interval = DateInterval::createFromDateString("-{$temp} days");
            $temp_date = $this->BASED_DATETIME->add($interval);

            $interval = DateInterval::createFromDateString('1 days');
            $this->LAST_WEEK_DATETIME_LIST = array($temp_date->add($interval));
            for ($i=1;$i<7;$i++){
                $this->LAST_WEEK_DATETIME_LIST[] = end($this->LAST_WEEK_DATETIME_LIST)->add($interval);
            }

            $this->CURRENT_WEEK_DATETIME_LIST = array(end($this->LAST_WEEK_DATETIME_LIST)->add($interval));
            for ($i=1;$i<7;$i++){
                $this->CURRENT_WEEK_DATETIME_LIST[] = end($this->CURRENT_WEEK_DATETIME_LIST)->add($interval);
            }

            $this->NEXT_WEEK_DATETIME_LIST = array(end($this->CURRENT_WEEK_DATETIME_LIST)->add($interval));
            for ($i=1;$i<7;$i++){
                $this->NEXT_WEEK_DATETIME_LIST[] = end($this->NEXT_WEEK_DATETIME_LIST)->add($interval);
            }
        }

        // public function: indicate if the date settled correctly
        /**
         * @return bool
         */
        public function is_correct(): bool {
            return $this->IS_CORRECT;
        }

        // public function: provide base time
        /**
         * @return bool|DateTimeImmutable
         */
        public function based_datetime(){
            if ($this->IS_CORRECT) {
                return $this->BASED_DATETIME;
            }

            return false;
        }

        // public function: provide the day of week of base time
        /**
         * @return bool|int
         */
        public function day_of_week() {
            if ($this->IS_CORRECT) {
                return $this->DAY_OF_WEEK_OF_BASED_DATETIME;
            }

            return false;
        }

        //public function: provide a datetime after given day of given base time
        /**
         * @param int $delta
         * @param DateTimeImmutable $base_date
         * @return DateTimeImmutable
         */
        public function date_after(int $delta, DateTimeImmutable $base_date): DateTimeImmutable
        {
            $interval = DateInterval::createFromDateString("{$delta} days");
            $base_date->add($interval);

            return $base_date;
        }

        //public function: provide a datetime before given day of given base time
        /**
         * @param int $delta
         * @param DateTimeImmutable $base_date
         * @return DateTimeImmutable
         */
        public function date_before(int $delta, DateTimeImmutable $base_date): DateTimeImmutable
        {
            $interval = DateInterval::createFromDateString("-{$delta} days");
            $base_date->add($interval);

            return $base_date;
        }
//
        // public function: provide all days of last week of base time with format "Y-m-d" like "2019-08-03"
        /**
         * @return bool|array
         */
        public function last_week() {
            if($this->IS_CORRECT) {
                $r = array();
                foreach ( $this->LAST_WEEK_DATETIME_LIST as $v ) {
                    /** @var DateTimeImmutable $v */
                    $r[] = $v->format('Y-m-d');
                }

                return $r;
            }

            return false;
        }

        // public function: provide all days of current week of base time with format "Y-m-d" like "2019-08-03"
        /**
         * @return bool|array
         */
        public function current_week() {
            if($this->IS_CORRECT) {
                $r = array();
                foreach ( $this->CURRENT_WEEK_DATETIME_LIST as $v ) {
                    /** @var DateTimeImmutable $v */
                    $r[] = $v->format('Y-m-d');
                }

                return $r;
            }

            return false;
        }

        // public function: provide all days of next week of base time with format "Y-m-d" like "2019-08-03"
        /**
         * @return bool|array
         */
        public function next_week()
        {
            if ($this->IS_CORRECT) {
                $r = array();
                foreach ($this->NEXT_WEEK_DATETIME_LIST as $v) {
                    /** @var DateTimeImmutable $v */
                    $r[] = $v->format('Y-m-d');
                }

                return $r;
            }

            return false;
        }
    }
}