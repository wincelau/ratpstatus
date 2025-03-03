<?php

abstract class Period
{
    protected $dateStart = null;

    public function isToday() {

        $date = (new DateTime())->modify('-3 hours');

        return $this->getDateStart()->format('Ymd') == $date->format('Ymd');
    }
}
