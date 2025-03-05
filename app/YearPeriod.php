<?php

class YearPeriod extends Period
{
    public function __construct($date) {
        $this->dateStart = DateTime::createFromFormat("Ymd", $date.'0101');
    }

    public function getDateFormat() {

        return 'Y';
    }

    public function getDateStartLabel() {

        return $this->getDateStart()->format('Y');
    }

    public function getDatePrevious() {

        return (clone $this->getDateStart())->modify('-1 year');
    }

    public function getDateNext() {

        return (clone $this->getDateStart())->modify('+1 year');
    }
}
