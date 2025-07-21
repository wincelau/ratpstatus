<?php

date_default_timezone_set('Europe/Paris');

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
            "trains" => [
                "*"       => ["API", "API"],
            ],
            "Ligne A" => [
                "FALLBACK"       => ["04:41:00", "01:41:00"], # Cergy le haut
            ],
            "Ligne B" => [
                "FALLBACK"       => ["04:47:00", "01:23:00"], # Mitry-Claye
            ],
            "Ligne C" => [
                "FALLBACK"       => ["03:38:00", "01:47:00"], # Dourdan - Saint-Martin d'Etampes
            ],
            "Ligne D" => [
                "FALLBACK"       => ["04:03:00", "01:51:00"], # Melun
            ],
            "Ligne E" => [
                "FALLBACK"       => ["04:52:00", "01:33:00"],
            ],
            "Ligne H" => [
                "FALLBACK"       => ["04:35:00", "01:43:00"],
            ],
            "Ligne J" => [
                "FALLBACK"       => ["04:20:00", "02:05:00"],
            ],
            "Ligne K" => [
                "FALLBACK"       => ["05:09:00", "23:40:00"],
            ],
            "Ligne L" => [
                "FALLBACK"       => ["04:38:00", "01:34:00"],
            ],
            "Ligne N" => [
                "FALLBACK"       => ["04:37:00", "01:41:00"],
            ],
            "Ligne P" => [
                "FALLBACK"       => ["04:50:00", "01:55:00"],
            ],
            "Ligne R" => [
                "FALLBACK"       => ["04:48:00", "01:45:00"],
            ],
            "Ligne U" => [
                "FALLBACK"       => ["05:20:00", "01:01:30"],
            ],
            "Ligne V" => [
                "FALLBACK"       => ["05:16:00", "23:52:00"],
            ],
            "tramways" => [
                "*"       => ["API", "API"],
            ],
            "T1" => [
                "FALLBACK"       => ["04:45:00", "02:39:00"],
            ],
            "T2" => [
                "FALLBACK"       => ["05:02:00", "02:35:00"],
            ],
            "T3A" => [
                "FALLBACK"       => ["05:15:00", "02:47:00"],
            ],
            "T3B" => [
                "FALLBACK"       => ["04:38:00", "02:42:00"],
            ],
            "T4" => [
                "FALLBACK"       => ["04:19:30", "01:58:30"],
            ],
            "T5" => [
                "FALLBACK"       => ["05:21:00", "01:54:00"],
            ],
            "T6" => [
                "FALLBACK"       => ["04:50:00", "02:12:00"],
            ],
            "T7" => [
                "FALLBACK"       => ["05:30:00", "01:04:00"],
            ],
            "T8" => [
                "FALLBACK"       => ["05:15:00", "01:53:00"],
            ],
            "T9" => [
                "FALLBACK"       => ["04:58:00", "02:27:00"],
            ],
            "T10" => [
                "FALLBACK"       => ["05:14:10", "02:07:30"],
            ],
            "T11" => [
                "FALLBACK"       => ["04:59:00", "01:34:10"],
            ],
            "T12" => [
                "FALLBACK"       => ["04:36:40", "00:49:10"],
            ],
            "T13" => [
                "FALLBACK"       => ["05:59:00", "00:41:59"],
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
                "Ligne V" => $baseUrlLogo."/v.svg",
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
