<?php

class MonthPeriod extends Period
{
    public function __construct($date) {
        $this->dateStart = DateTime::createFromFormat("Ymd", $_GET['date'].'01');
    }

    public function getDateFormat() {

        return 'Ym';
    }

    public function getDateStartLabel() {

        return View::displayDateMonthToFr($this->getDateStart(), 4);
    }

    public function getDatePrevious() {

        return (clone $this->getDateStart())->modify('-1 month');
    }

    public function getDateNext() {

        return (clone $this->getDateStart())->modify('+1 month');
    }
}
