<?php

class View {

    public static function url($url) {
        if($GLOBALS['isStaticResponse']) {

            return $url;
        }

        preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

        $script = "index.php";

        if(strlen($matches[1]) == "6") {
            $script = "month.php";
        }

        return $script."?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
    }

    public static function getDatesChoices() {
        $date = (new DateTime())->modify('-3 hours');
        $dates = [];
        $dates[$date->format('Ymd')] = "Aujourd'hui";
        $date->modify('-1 day');
        $dates[$date->format('Ymd')] = "Hier";
        while($date->format('Ym') >= "202404") {
            $dates[$date->format('Ym')] = self::convertMonthToFr($date->format('M Y'));
            $date->modify('-1 month');
        }
        return $dates;
    }

    public static function convertMonthToFr($label) {
        $labels = [
            "Jan" => "Janv",
            "Feb" => "Févr",
            "Mar" => "Mars",
            "Apr" => "Avr.",
            "May" => "Mai",
            "Jun" => "Juin",
            "Jul" => "Juil",
            "Aug" => "Août",
            "Sep" => "Sept",
            "Oct" => "Oct.",
            "Nov" => "Nov.",
            "Dec" => "Déc.",
        ];

        return str_replace(array_keys($labels), array_values($labels), $label);
    }
}
