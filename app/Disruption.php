<?php

class Disruption
{
    protected $impacts = [];
    protected $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addImpact($impact) {
        if(isset($this->impacts[$impact->getId()])) {
            $impact->setDateCreation($this->impacts[$impact->getId()]->getDateCreation());
        }
        $this->impacts[$impact->getId()] = $impact;
    }

    public function getImpactsInPeriod($date) {
        $impacts = [];

        foreach($this->impacts as $impact) {
            if(!$impact->isInPeriod($date)) {
                continue;
            }
            $impacts[$impact->getDateCreation()->format('YMDHis').$impact->getId()] = $impact;
        }

        return $impacts;
    }
}
