<?php

namespace Icinga\Module\Munin;

class Periods {
    public static function getPeriods() {
        $periods = [
                     'day',
                     'week',
                     'month',
                     'year',
                   ];

        return $periods;
    }

    public static function getPeriodDays() {
        $period_days = [
                         'day'   => 1,
                         'week'  => 7,
                         'month' => 30,
                         'year'  => 365,
                       ];

        return $period_days;
    }

    public static function getPeriodicity() {
        $periodicity = [
                         'day'   => 'daily',
                         'week'  => 'weekly',
                         'month' => 'monthly',
                         'year'  => 'yearly',
                       ];

        return $periodicity;
    }
}

?>
