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

        if(preg_match("/(modifications horaires|horaires modifiés|Modifications d'horaires|Changement d'horaires?|modification horaire|Changement de service)/i", $this->getTitle())) {
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
        if(in_array($this->getMode(), [self::MODE_METRO, self::MODE_TRAMWAY]) && preg_match("/:[^:]* - /", $this->getTitle()) && !in_array($impact->getLigne()->getName(), ["T4", "T12", "T13"])) {

            return preg_replace('/ - .*$/', '', preg_replace('/^[^:]*: /', '', $this->getTitle()));
        }


        if(preg_match("/Motif[\s]*:?[\s]*([^\n]*)(\n|$)/i", $this->getMessagePlainText(), $matches)) {

            $matches[1] = preg_replace("/Motif[ ]*:?[ ]*/", '', $matches[1]);

            if(preg_match("/accident à un passage à niveau/i", $matches[1])) {
                return "Accident à un passage à niveau";
            }
            if(preg_match("/accident de personne/i", $matches[1])) {
                return "Accident de personne";
            }
            if(preg_match("/accident routier/i", $matches[1])) {
                return "Accident routier";
            }
            if(preg_match("/acte de vandalis?me/i", $matches[1])) {
                return "Acte de vandalisme";
            }
            if(preg_match("/acte de malveillance/i", $matches[1])) {
                return "Acte de malveillance";
            }
            if(preg_match("/affaires?\s*oubli[ée]*s?/i", $matches[1])) {
                return "Affaires oubliées";
            }
            if(preg_match("/accident à un passage à niveau/i", $matches[1])) {
                return "Accident à un passage à niveau";
            }
            if(preg_match("/^agression.*(agent|conduct)/i", $matches[1])) {
                return "Agression d'un agent";
            }
            if(preg_match("/^agression.*voyageu/i", $matches[1])) {
                return "Agression d'un voyageur";
            }
            if(preg_match("/panne (d'un|du) passage à niveau/i", $matches[1])) {
                return "Panne d'un passage à niveau";
            }
            if(preg_match("/altercation entre voyageurs/i", $matches[1])) {
                return "Altercation entre voyageurs";
            }
            if(preg_match("/déclenchement du signal d'alarme/i", $matches[1])) {
                return "Déclenchement du signal d'alarme";
            }
            if(preg_match("/^heurt.*anima/i", $matches[1])) {
                return "Heurt d'un animal";
            }
            if(preg_match("/^heurt.*camion/i", $matches[1])) {
                return "Heurt d'un véhicule";
            }
            if(preg_match("/(anima|mouton).*voie/i", $matches[1])) {
                return "Animal sur la voie";
            }
            if(preg_match("/alerte? de s[eé]*curité/i", $matches[1])) {
                return "Alerte de sécurité émise par le conducteur";
            }
            if(preg_match("/choc.*vérification/i", $matches[1])) {
                return "Choc nécessitant une vérification technique sur le train";
            }
            if(preg_match("/pannes? (d'un|sur le|du|d’un|de|des) (train|TER)/i", $matches[1])) {
                return "Panne d'un train";
            }
            if(preg_match("/pannes? (d'un|du|de) tram/i", $matches[1]) || preg_match("/Tram.* en panne/i", $matches[1]) || preg_match("/rame en panne/i", $matches[1])) {
                return "Tramway en panne";
            }
            if(preg_match("/conditions?\s*de\s*départ/i", $matches[1])) {
                return "Conditions de départ non réunies";
            }
            if(preg_match("/malaise.*voyageur/i", $matches[1])) {
                return "Malaise voyageur";
            }
            if(preg_match("/Attente d'autorisation d'accès au réseau/i", $matches[1])) {
                return "Attente d'autorisation d'accès au réseau";
            }
            if(preg_match("/alerte.*mise*conducteur/i", $matches[1])) {
                return "Alerte de sécurité émise par le conducteur";
            }
            if(preg_match("/individu.*voie/i", $matches[1])) {
                return "Individus sur les voies";
            }
            if(preg_match("/Panne.*install.*gestion.*r/i", $matches[1])) {
                return "Panne sur les installations du gestionnaire de réseau";
            }
            if(preg_match("/Panne.*install.*gestion.*r/i", $matches[1])) {
                return "Panne sur les installations du gestionnaire de réseau";
            }
            if(preg_match("/condition.*(météo|climatique)/i", $matches[1]) || preg_match("/(tempête|intempéries|dépression)/i", $matches[1])) {
                return "Conditions météorologiques";
            }
            if(preg_match("/personnel/i", $matches[1])) {
                return "Difficultés liées à un manque de personnel";
            }
            if(preg_match("/incident.*voyageur/i", $matches[1])) {
                return "Incident voyageur";
            }
            if(preg_match("/incident.*technique/i", $matches[1])) {
                return "Incident technique";
            }
            if(preg_match("/incident.*signalisation/i", $matches[1])) {
                return "Incident affectant la signalisation";
            }
            if(preg_match("/incident.*voie/i", $matches[1])) {
                return "Incident affectant la voie";
            }
            if(preg_match("/panne.*signalisation/i", $matches[1])) {
                return "Panne de signalisation";
            }
            if(preg_match("/^Défaut de signalisation.*/i", $matches[1])) {
                return "Défaut de signalisation";
            }
            if(preg_match("/respect.*distances/i", $matches[1])) {
                return "Respect des distances de sécurité";
            }
            if(preg_match("/d.{1}faut.*lectrique/i", $matches[1])) {
                return "Défaut d'alimentation électrique";
            }
            if(preg_match("/coupure.*lectrique/i", $matches[1])) {
                return "Coupure d'alimentation électrique";
            }
            if(preg_match("/panne.*lectrique/i", $matches[1])) {
                return "Panne électrique";
            }
            if(preg_match("/heurt.*obstacle/i", $matches[1])) {
                return "Heurt d'un obstacle sur la voie";
            }
            if(preg_match("/obstacle.*voie/i", $matches[1])) {
                return "Obstacle sur la voie";
            }
            if(preg_match("/mouvement.*social/i", $matches[1])) {
                return "Mouvement social";
            }
            if(preg_match("/^mesure.*r.{1}gulation/i", $matches[1])) {
                return "Mesures de régulation";
            }
            if(preg_match("/^R.{1}gulation.*trafic/i", $matches[1])) {
                return "Régulation de trafic";
            }
            if(preg_match("/^panne.*passage.*niveau/i", $matches[1])) {
                return "Panne d'un passage à niveau";
            }
            if(preg_match("/^obstacle.*passage.*niveau/i", $matches[1])) {
                return "Obstacle sur un passage à niveau";
            }
            if(preg_match("/bagage.*(oubli|aband|délaissé).*train/i", $matches[1])) {
                return "Bagage oublié dans un train";
            }
            if(preg_match("/bagage.*(oubli|aband|délaissé).*(quai|station)/i", $matches[1])) {
                return "Bagage oublié sur un quai";
            }
            if(preg_match("/bagage.*(oubli|aband|délaissé)/i", $matches[1])) {
                return "Bagage oublié";
            }
            if(preg_match("/jeu.*paris.*2024/i", $matches[1])) {
                return "Jeux de Paris 2024";
            }
            if(preg_match("/signal.*alarme/i", $matches[1])) {
                return "Actionnement d'un signal d'alarme";
            }
            if(preg_match("/Indisponibilit.*mat/i", $matches[1])) {
                return "Indisponibilité materiel";
            }
            if(preg_match("/Indisponibilit.*agent/i", $matches[1])) {
                return "Indisponibilité d'agents";
            }
            if(preg_match("/^prolongation des travaux.*ferro/i", $matches[1])) {
                return "Prolongation des travaux sur le réseau ferroviaire";
            }
            if(preg_match("/^prolongation des travaux/i", $matches[1])) {
                return "Prolongation des travaux effectués par le gestionnaire de réseau";
            }
            if(preg_match("/^difficult.*(transporteur|gestionnaire).*bus/i", $matches[1])) {
                return "Difficultés liées au transporteur de bus";
            }
            if(preg_match("/^difficult.*circulation/i", $matches[1])) {
                return "Difficultés de circulation";
            }
            if(preg_match("/^G.ne.*circulation/i", $matches[1])) {
                return "Difficultés de circulation";
            }
            if(preg_match("/^travaux.*maintenance/i", $matches[1])) {
                return "Travaux de maintenance";
            }
            if(preg_match("/modernisation/i", $matches[1])) {
                return "Travaux de modernisation";
            }
            if(preg_match("/^travaux.*/i", $matches[1])) {
                return "Travaux";
            }
            if(preg_match("/mesure.*sécu/i", $matches[1])) {
                return "Mesures de sécurité";
            }
            if(preg_match("/trafic pertur.*gestionnaire/i", $matches[1])) {
                return "Trafic perturbé du fait du gestionnaire de réseau";
            }

            return ucfirst(trim(preg_replace('/(à|entre|aux?|à la)\s+[A-Z]{1}.*$/', '', preg_replace("/( dans le secteur.*$| en gare d.*$| dans un train à.*$| à bord .*$| aux abords d.*$| au garage de.*$| entre les gares de.*$| à hauteur de.*$| sur un pont.*$| sur le pont.*$| au départ de.*$| sur la ligne.*$|\(.*$|\..*$)/i", '', $matches[1]))));
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

        $suggestionType = $this->getSuggestionType();

        if($suggestionType == self::TYPE_AUCUNE) {
            return true;
        }

        if($suggestionType == self::TYPE_CHANGEMENT_HORAIRES) {
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
