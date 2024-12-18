<?php

class Config
{
    public static function getModeLibelles() {
        return ["metros" => "Ⓜ️ <span>Métros</span>", "trains" => "🚆 <span>RER/Trains</span>", "tramways" => "🚈 <span>Tramways</span>"];
    }

    public static function getOpeningTime() {
        return [
            "metros" => [
                "*"       => ["05:30:00", "01:15:00"],
                "(Fri|Sat)" => ["05:30:00", "02:15:00"]
            ],
            "Métro 3B" => [
                "*"       => ["05:27:00", "01:15:00"],
                "(Fri|Sat)" => ["05:27:00", "02:15:00"]
            ],
            "Métro 7" => [
                "*"       => ["05:28:00", "01:15:00"],
                "(Fri|Sat)" => ["05:28:00", "02:15:00"]
            ],
            "Métro 8" => [
                "*"       => ["05:21:00", "01:15:00"],
                "(Fri|Sat)" => ["05:21:00", "02:15:00"]
            ],
            "Ligne A" => [
                "*"       => ["04:41:00", "01:41:00"], # Cergy le haut
            ],
            "Ligne B" => [
                "*"       => ["04:47:00", "01:23:00"], # Mitry-Claye
            ],
            "Ligne C" => [
                "*"       => ["03:38:00", "01:47:00"], # Dourdan - Saint-Martin d'Etampes
            ],
            "Ligne D" => [
                "*"       => ["04:03:00", "01:51:00"], # Melun
            ],
            "Ligne E" => [
                "*"       => ["04:52:00", "01:33:00"],
            ],
            "Ligne H" => [
                "*"       => ["04:35:00", "04:32:00"],
            ],
            "Ligne J" => [
                "*"       => ["04:20:00", "02:05:00"],
            ],
            "Ligne K" => [
                "*"       => ["05:09:00", "23:40:00"],
            ],
            "Ligne L" => [
                "*"       => ["04:38:00", "01:34:00"],
            ],
            "Ligne N" => [
                "*"       => ["04:37:00", "01:41:00"],
            ],
            "Ligne P" => [
                "*"       => ["04:50:00", "01:55:00"],
            ],
            "Ligne R" => [
                "*"       => ["04:48:00", "01:45:00"],
            ],
            "Ligne U" => [
                "*"       => ["05:20:00", "01:01:30"],
            ],
        ];
    }

    public static function getLignes() {
        $baseUrlLogo = "/images/lignes/";

        return [
            "metros" => [
                "Métro 1" => $baseUrlLogo."/1.svg",
                "Métro 2" => $baseUrlLogo."/2.svg",
                "Métro 3" => $baseUrlLogo."/3.svg",
                "Métro 3B" => $baseUrlLogo."/3b.svg",
                "Métro 4" => $baseUrlLogo."/4.svg",
                "Métro 5" => $baseUrlLogo."/5.svg",
                "Métro 6" => $baseUrlLogo."/6.svg",
                "Métro 7" => $baseUrlLogo."/7.svg",
                "Métro 7B" => $baseUrlLogo."/7b.svg",
                "Métro 8" => $baseUrlLogo."/8.svg",
                "Métro 9" => $baseUrlLogo."/9.svg",
                "Métro 10" => $baseUrlLogo."/10.svg",
                "Métro 11" => $baseUrlLogo."/11.svg",
                "Métro 12" => $baseUrlLogo."/12.svg",
                "Métro 13" => $baseUrlLogo."/13.svg",
                "Métro 14" => $baseUrlLogo."/14.svg",
            ],
            "trains" => [
                "Ligne A" => $baseUrlLogo."/a.svg",
                "Ligne B" => $baseUrlLogo."/b.svg",
                "Ligne C" => $baseUrlLogo."/c.svg",
                "Ligne D" => $baseUrlLogo."/d.svg",
                "Ligne E" => $baseUrlLogo."/e.svg",
                "Ligne H" => $baseUrlLogo."/h.svg",
                "Ligne J" => $baseUrlLogo."/j.svg",
                "Ligne K" => $baseUrlLogo."/k.svg",
                "Ligne L" => $baseUrlLogo."/l.svg",
                "Ligne N" => $baseUrlLogo."/n.svg",
                "Ligne P" => $baseUrlLogo."/p.svg",
                "Ligne R" => $baseUrlLogo."/r.svg",
                "Ligne U" => $baseUrlLogo."/u.svg",
            ],
            "tramways" => [
                "T1" => $baseUrlLogo."/t1.svg",
                "T2" => $baseUrlLogo."/t2.svg",
                "T3A" => $baseUrlLogo."/t3a.svg",
                "T3B" => $baseUrlLogo."/t3b.svg",
                "T4" => $baseUrlLogo."/t4.svg",
                "T5" => $baseUrlLogo."/t5.svg",
                "T6" => $baseUrlLogo."/t6.svg",
                "T7" => $baseUrlLogo."/t7.svg",
                "T8" => $baseUrlLogo."/t8.svg",
                "T9" => $baseUrlLogo."/t9.svg",
                "T10" => $baseUrlLogo."/t10.svg",
                "T11" => $baseUrlLogo."/t11.svg",
                "T12" => $baseUrlLogo."/t12.svg",
                "T13" => $baseUrlLogo."/t13.svg",
            ]
        ];
    }

    public static function getTypesPerturbation() {
        return [
        Impact::TYPE_PERTURBATION_PARTIELLE => "Perturbation partielle",
        Impact::TYPE_PERTURBATION_TOTALE =>"Perturbation sur l'ensemble de la ligne",
        Impact::TYPE_PERTURBATION_TOTALE_FORTE => "Forte perturbation",
        Impact::TYPE_INTERRUPTION_PARTIELLE => "Interruption partielle",
        Impact::TYPE_INTERRUPTION_TOTALE => "Interruption sur l'ensemble de la ligne",
        Impact::TYPE_STATIONS_NON_DESSERVIES => "Station(s) non desservie(s)",
        Impact::TYPE_GARES_NON_DESSERVIES => "Gare(s) non desservie(s)",
        Impact::TYPE_TRAINS_STATIONNENT => "Trains stationnent",
        Impact::TYPE_TRAINS_SUPPRIMES => "Trains supprimés",
        Impact::TYPE_CHANGEMENT_HORAIRES => "Changement d'horaires",
        Impact::TYPE_CHANGEMENT_COMPOSITION => "Changement de composition",
        Impact::TYPE_AUCUNE => "Aucune perturbation en cours",
        ];
    }
}
