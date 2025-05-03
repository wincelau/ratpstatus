<?php

class YearPeriod extends Period
{
    protected $key = null;

    public function __construct($date) {
        if($date == '12derniersmois') {
            $date = new DateTime();
            $date->modify('first day of this month');
            $date->modify('-1 year');
            $date = $date->format('Ymd');
            $this->key = "12derniersmois";
        } else {
            $date .= '0101';
        }
        $this->dateStart = DateTime::createFromFormat("YmdHis", $date.'000000');
    }

    public function isToday() {
        if($this->key == "12derniersmois") {
            return true;
        }

        return parent::isToday();
    }

    public function getDateFormat() {

        return 'Y';
    }

    public function getDateStartKey() {
        if(!is_null($this->key)) {

            return $this->key;
        }

        return parent::getDateStartKey();
    }

    public function getDateStartLabel() {
        if($this->key == "12derniersmois") {

            return "12 dern mois";
        }

        return $this->getDateStart()->format('Y');
    }

    public function getDatePrevious() {

        return (clone $this->getDateStart())->modify('-1 year');
    }

    public function getDateNext() {

        return (clone $this->getDateStart())->modify('+1 year');
    }
}
