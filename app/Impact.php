<?php

class Impact
{
    public $data = null;
    public $ligne = null;

    const CAUSE_TRAVAUX = 'TRAVAUX';
    const CAUSE_PERTURBATION = 'PERTURBATION';
    const SEVERITY_PERTURBEE = 'PERTURBEE';
    const SEVERITY_BLOQUANTE = 'BLOQUANTE';
    const SEVERITY_INFORMATION = 'INFORMATION';

    const TYPE_RALENTI = 'RALENTI';
    const TYPE_RALENTI_FORTEMENT = 'RALENTI_FORTEMENT';
    const TYPE_PERTURBATION_PARTIELLE = 'PERTURBATION_PARTIELLE';
    const TYPE_PERTURBATION_PARTIELLE_FORTE = 'PERTURBATION_PARTIELLE_FORTE';
    const TYPE_PERTURBATION_TOTALE = 'PERTURBATION_TOTALE';
    const TYPE_PERTURBATION_TOTALE_FORTE = 'PERTURBATION_TOTALE_FORTE';
    const TYPE_PERTURBATION_TOTALE_REPRISE = 'PERTURBATION_TOTALE_REPRISE';
    const TYPE_INTERRUPTION_PARTIELLE = 'INTERRUPTION_PARTIELLE';
    const TYPE_INTERRUPTION_TOTALE = 'INTERRUPTION_TOTALE';
    const TYPE_STATIONS_NON_DESSERVIES = 'STATIONS_NON_DESSERVIES';
    const TYPE_GARES_NON_DESSERVIES = 'GARES_NON_DESSERVIES';
    const TYPE_TRAINS_STATIONNENT = 'STATIONS_NON_DESSERVIES';
    const TYPE_TRAINS_SUPPRIMES = 'TRAINS_SUPPRIMES';
    const TYPE_CHANGEMENT_HORAIRES = 'CHANGEMENT_HORAIRES';
    const TYPE_CHANGEMENT_COMPOSITION = 'CHANGEMENT_COMPOSITION';
    const TYPE_AUCUNE = 'AUCUNE';

    const MODE_RER = 'RapidTransit';
    const MODE_TRAIN = 'LocalTrain';
    const MODE_METRO = 'Metro';
    const MODE_TRAMWAY = 'Tramway';

    protected $dateStart = null;
    protected $dateEnd = null;
    protected $type = null;
    protected $dateCreation = null;

    public function __construct($data, File $file) {
        $this->data = $data;
        foreach($this->data->applicationPeriods as $period) {
            $this->dateEnd = $period->end;
            if($this->dateStart && $period->begin > $this->dateStart && $period->begin < $this->dateEnd) {
                continue;
            }
            $this->dateStart = $period->begin;
        }

        $this->dateCreation = $file->getDate();

        $userFile = __DIR__.'/../datas/json_userinfos/'.(clone $file->getDate())->modify('-3 hours')->format('Ymd').'.json';
        if(is_file($userFile)) {
            $userDisruptions = (array) json_decode(file_get_contents($userFile));
            if(isset($userDisruptions[$this->getId()])) {
                $this->type = $userDisruptions[$this->getId()]->type;
            }
        }
    }

    public function getId() {

        return $this->data->id;
    }

    public function setLigne($ligne) {

        $this->ligne = $ligne;
    }

    public function getLigne() {

        return $this->ligne;
    }

    public function hasRealDisruptionId() {

        return isset($this->data->disruption_id);
    }

    public function getDistruptionId() {
        if($this->hasRealDisruptionId()) {

            return "distruption_id:".$this->data->disruption_id;
        }

        return $this->getDistruptionIdCalculate();
    }

    public function getDistruptionIdCalculate() {
        if(in_array($this->getMode(), [self::MODE_RER, self::MODE_TRAIN])) {
             return "distruption_id_calculate:".md5($this->getId());
        }
        return "distruption_id_calculate:".md5($this->getUniqueTitle());
    }

    public function setDateCreation($date) {

        return $this->dateCreation = $date;
    }

    public function getDateCreation() {

        return $this->dateCreation;
    }

    public function getTitle() {

        return $this->data->title;
    }

    public function isSameImpact($impact) {
        if(in_array($this->getMode(), [self::MODE_METRO, self::MODE_TRAMWAY])) {

            return $this->getUniqueTitle() == $impact->getUniqueTitle();
        }

        if(in_array($this->getMode(), [self::MODE_RER, self::MODE_TRAIN])) {

            return $this->getTitle().$this->getSeverity() == $impact->getTitle().$impact->getSeverity() && ($this->isInPeriod($impact->getDateStart()) || $this->isInPeriod($impact->getDateEnd()));
        }
    }

    public function getUniqueTitle() {
        return str_replace([" - Reprise progressive / trafic reste très perturbé", " - Reprise progressive / trafic reste perturbé", " - Arrêt non desservi", " - Reprise progressive"," - Stationnement prolongé", " - Trafic interrompu", " - Trafic perturbé", " - Trafic très perturbé", " - Trains stationnent", " - Train stationne"], "", $this->getTitle());
    }

    public function getSuggestionType() {
        if(preg_match("/(trafic sera très perturbé|trafic sera interrompu|le trafic de la ligne [A-Z0-9]+ sera perturbé|le trafic de la ligne [A-Z0-9]+ sera interrompu|trafic sera également interrompu|trafic de la ligne sera légèrement perturbé)/i", $this->getMessagePlainText())) {

            //return self::TYPE_AUCUNE;
        }

        if(preg_match("/(rendez-vous la veille|pourraient perturber la circulation|pourrait perturber la circulation)/i", $this->getMessagePlainText())) {

            return self::TYPE_AUCUNE;
        }

        if(preg_match("/Le trafic est fortement perturbé[àéèîếa-zA-z\ '0-9]*entre/i", $this->getMessagePlainText())) {

            return self::TYPE_PERTURBATION_PARTIELLE_FORTE;
        }
        if(preg_match("/Le trafic est fortement perturbé sur l'ensemble de la ligne/i", $this->getMessagePlainText())) {

            return self::TYPE_PERTURBATION_TOTALE_FORTE;
        }

        if(preg_match("/Le trafic reprend mais reste perturbé sur l'ensemble de la ligne/i", $this->getMessagePlainText())) {

            return self::TYPE_PERTURBATION_TOTALE_REPRISE;
        }

        if(preg_match('/Le trafic est interrompu entre/i', $this->getMessagePlainText())) {

            return self::TYPE_INTERRUPTION_PARTIELLE;
        }

        if(preg_match("/trafic (est |)interrompu sur l'ensemble de la ligne/i", $this->getMessagePlainText())) {

            return self::TYPE_INTERRUPTION_TOTALE;
        }

        if(preg_match("/Le trafic est perturbé sur l'ensemble de la ligne/i", $this->getMessagePlainText())) {

            return self::TYPE_PERTURBATION_TOTALE;
        }

        if(preg_match("/Le trafic est perturbé[àéèîếa-zA-z\ '0-9]*entre/i", $this->getMessagePlainText())) {

            return self::TYPE_PERTURBATION_PARTIELLE;
        }

        if(preg_match("/(Trains|Tramways)?[a-zA-z\ ]*supprimés?/i", $this->getMessagePlainText())) {

            return self::TYPE_TRAINS_SUPPRIMES;
        }

        if(preg_match("/Gares? non desservies?/i", $this->getTitle())) {

            return self::TYPE_GARES_NON_DESSERVIES;
        }

        if(preg_match("/Le trafic est fortement ralenti/i", $this->getMessagePlainText())) {

            return self::TYPE_RALENTI_FORTEMENT;
        }

        if(preg_match("/Le trafic est ralenti/i", $this->getMessagePlainText())) {

            return self::TYPE_RALENTI;
        }

        if(preg_match("/ralenti/i", $this->getTitle())) {

            return self::TYPE_RALENTI;
        }

        if(preg_match("/arrêt bus de remplacement/i", $this->getTitle())) {

            return self::TYPE_AUCUNE;
        }

        if(preg_match('/(Alerte orages|Alerte forte pluies et orages|Vigilance orange Météo France)/i', $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match("/risquent de perturber le trafic/i", $this->getMessagePlainText())) {

            return self::TYPE_AUCUNE;
        }

        if(preg_match("/risque de perturbation/i", $this->getTitle())) {

            return self::TYPE_AUCUNE;
        }

        if(preg_match('/Modifications? de compositions?/i', $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match('/adaptation/i', $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match('/adaptation des circulations/i', $this->getOrigine())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match('/offre de transport est adaptée/i', $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match('/offre de transport est adaptée/i', $this->getMessagePlainText())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match("/(modifications horaires|horaires modifiés|Modifications d'horaires|Changement d'horaires?|modification horaire|Changement de service|)/i", $this->getTitle())) {
            return self::TYPE_CHANGEMENT_HORAIRES;
        }

        if(preg_match("/(L'|Les )horaires? (de certains|des) trains[^\n\.]*(sont|est) avancés? ou retardés?/", $this->getMessagePlainText())) {
            return self::TYPE_CHANGEMENT_HORAIRES;
        }

        if(preg_match('/Modification de desserte/', $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match('/train court/i', $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match("/(modification arrêt de bus|Emplacement des bus de remplacement)/i", $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match("/baisse des températures/i", $this->getTitle())) {
            return self::TYPE_AUCUNE;
        }

        if(preg_match("/(L'|Les )arrêts? des bus de remplacement.*(est déplacé|se situe désormais|est définitivement reporté|sont déplacés)/", $this->getMessagePlainText())) {
            return self::TYPE_AUCUNE;
        }

        return null;
    }

    public function getOrigine() {

        return $this->getSuggestionOrigine();
    }

    public function getSuggestionOrigine() {
        if(in_array($this->getMode(), [self::MODE_METRO, self::MODE_TRAMWAY]) && preg_match("/:[^:]* - /", $this->getTitle())) {

            return preg_replace('/ - .*$/', '', preg_replace('/^[^:]*: /', '', $this->getTitle()));
        }

        if(preg_match("/Motif[\s]*:[\s]*([^\n]*)(\n|$)/i", $this->getMessagePlainText(), $matches)) {

            return ucfirst(trim(preg_replace('/(à|entre|aux?|à la)\s+[A-Z]{1}.*$/', '', preg_replace("/( dans le secteur.*$| en gare d.*$| dans un train à.*$| à bord du train.*$| aux abords d.*$| au garage de.*$| entre les gares de.*$| à hauteur de.*$|\(.*$|\..*$)/i", '', $matches[1]))));
        }

        return null;
    }

    public function getMessage() {

        return $this->data->message;
    }

    public function getCause() {

        return $this->data->cause;
    }

    public function getSeverity() {

        return $this->data->severity;
    }

    public function getType() {

        return $this->type;
    }

    public function getColorClass() {
        $cssClass = 'ok';

        if($this->getCause() == Impact::CAUSE_PERTURBATION && $this->getSeverity() == Impact::SEVERITY_BLOQUANTE) {
            $cssClass = 'bq';
        }
        if($cssClass == 'ok' && $this->getCause() == Impact::CAUSE_TRAVAUX) {
            $cssClass = 'tx';
        }
        if($this->getCause() == Impact::CAUSE_PERTURBATION && $this->getSeverity() == Impact::SEVERITY_PERTURBEE) {
            $cssClass = 'pb';
        }

        return $cssClass;
    }

    public function getLigneId() {
        return preg_replace('/^[^ ]+ /', '', strtoupper(implode("", $this->getLignes())));
    }

    public function getMode() {
        return preg_replace('/ [^ ]+$/', '', implode("", $this->getLignes()));
    }

    public function getLignes() {

        return isset($this->data->lines) ? $this->data->lines : [];
    }

    public function isToExclude() {
        if($this->getSeverity() == self::SEVERITY_INFORMATION) {
            return true;
        }

        if($this->getType() == self::TYPE_AUCUNE) {
            return true;
        }

        if($this->getType() == self::TYPE_CHANGEMENT_HORAIRES) {
            return true;
        }

        if($this->getSuggestionType() == self::TYPE_AUCUNE) {
            return true;
        }

        if($this->getSuggestionType() == self::TYPE_CHANGEMENT_HORAIRES) {
            return true;
        }

        if(!count($this->getLignes())) {
            return true;
        }

        return false;
    }

    public function getDateStart() {
        $date = DateTime::createFromFormat('Ymd\THis', $this->dateStart);

        if($this->getLigne() && $this->getLigne()->getOpeningDateTime() && $this->getDateEnd() < $this->getLigne()->getOpeningDateTime()) {
            return $this->getDateEnd();
        }

        if($this->getLigne() && $this->getLigne()->getOpeningDateTime() && $date < $this->getLigne()->getOpeningDateTime()) {
            return $this->getLigne()->getOpeningDateTime();
        }

        return $date;
    }

    public function setDateStart($date) {

        return $this->dateStart = $date;
    }

    public function getDateEnd() {
        $date = DateTime::createFromFormat('Ymd\THis', $this->dateEnd);

        if($this->getLigne() && $this->getLigne()->getClosingDateTime() && $date > $this->getLigne()->getClosingDateTime()) {
            return $this->getLigne()->getClosingDateTime();
        }

        return $date;
    }

    public function setDateEnd($date) {

        return $this->dateEnd = $date;
    }

    public function getDuration() {
        $dateEnd = $this->getDateEnd();
        if($this->getDateEnd() > new DateTime()) {
           $dateEnd = new DateTime();
        }
        return Disruption::calculateTotalDuration([['start' => $this->getDateStart()->format('Y-m-d H:i:s'), 'end' => $dateEnd->format('Y-m-d H:i:s')]]);
    }

    public static function generateDurationText($second) {
        $nbMinutes = round($second / 60);
        $nbHours = round($second / 3600);

        if($nbMinutes < 180) {

            return sprintf("%d min", $nbMinutes);
        }

        return sprintf("%d h", $nbHours);
    }

    public static function generateDurationMinutes($second) {
        $nbMinutes = round($second / 60, 2);

        if(!$nbMinutes) {
            return null;
        }

        return $nbMinutes;
    }

    public function getDurationText() {

        return self::generateDurationText($this->getDuration());
    }

    public function getDurationMinutes() {

        return self::generateDurationMinutes($this->getDuration());
    }

    public function isInProgress() {
        $current = new DateTime();

        return $current > $this->getDateStart() && $current < $this->getDateEnd();
    }

    public function isInPeriod(DateTime $date) {

        return $date >= $this->getDateStart() && $date <= $this->getDateEnd();
    }

    public function getMessagePlainText() {
        $message = trim(preg_replace('/[^\S\n]+/', ' ', str_replace(chr(194).chr(160), " ", str_replace('"', '', html_entity_decode(strip_tags(str_replace("<br>", "\n", $this->getMessage())))))));

        $message = preg_replace("/(Pour plus d'informations sur cette perturbation, consultez le fil Twitter de[^\.]+\.|Plus d'informations sur le site ratp\.fr|Rendez-vous sur la rubrique Recherche Itinéraire, pour retrouver un itinéraire prenant en compte cette perturbation\.)[^\n]*[\n]*/", "", $message);

        return $message;
    }

    public function getLastUpdate() {
        return DateTime::createFromFormat('Ymd\THis', $this->data->lastUpdate);
    }
}
