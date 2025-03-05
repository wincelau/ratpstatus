<?php

class MonthPeriod extends Period
{
    public function __construct($date) {
        $this->dateStart = DateTime::createFromFormat("Ymd", $date.'01');
    }

    public function getDateFormat() {

        return 'Ym';
    }

    public function getDateStartLabel() {

        return View::displayDateMonthToFr($this->getDateStart(), true)." ".$this->getDateStart()->format('Y');
    }

    public function getDatePrevious() {

        return (clone $this->getDateStart())->modify('-1 month');
    }

    public function getDateNext() {

        return (clone $this->getDateStart())->modify('+1 month');
    }
}
