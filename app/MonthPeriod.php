<?php

class MonthPeriod extends Period
{
    public function __construct($date) {
        $this->dateStart = DateTime::createFromFormat("Ymd", $_GET['date'].'01');
    }

    public function getDateStart() {

        return $this->dateStart;
    }
}
