<?php

namespace Icinga\Module\Munin;

use Icinga\Application\Logger;

class Limits
{
    public static function parseFile($file)
    {
        $limits = [];

        if(is_file($file) && is_readable($file)) {
            $content = file_get_contents($file);

            if($content === FALSE) {
                Logger::error("Failed to read limits: $file");
            }
            else {
                foreach(preg_split('/\r?\n/', $content) as $line) {
                    # version 2.0.33-1
                    if(preg_match('/^(version)\s+(\S+.*?\S+)\s*$/', $line, $matches)) {
                        $key   = $matches[1];
                        $value = $matches[2];

                        $limits[$key] = $value;
                    }
                    # linuxminded.xs4all.nl;anubis.linuxminded.xs4all.nl;smart_sda;Offline_Uncorrectable;ok OK
                    elseif(preg_match('/^(\S+?);(\S+);(\S+?);(\S+);(\S+) (\S+.*?)\s*$/', $line, $matches)) {
                        $group      = $matches[1];
                        $host       = $matches[2];
                        $plugin     = $matches[3];
                        $datasource = $matches[4];
                        $key        = $matches[5];
                        $value      = $matches[6];

                        $limits['_group'][$group][$host][$plugin][$datasource][$key] = $value;
                    }
                    else {
                        Logger::warning("Cannot parse line: $line");
                    }
                }
            }
        }
        else {
            Logger::error("Cannot read limits: $file");
        }

        return $limits;
    }

    public static function getProblems()
    {
        $problems = [
                      'critical',
                      'warning',
                      'unknown',
                    ];

        return $problems;
    }

    public static function getProblemTitle()
    {
        $problem_title = [
                           'critical' => 'Critical',
                           'warning'  => 'Warning',
                           'unknown'  => 'Unknown',
                         ];

        return $problem_title;
    }

    public static function getProblemCount($limits, $data)
    {
        $problems = static::getProblems();

        $problem_count = [];

        foreach($problems as $problem) {
            $problem_count[$problem] = 0;
        }

        if(
            array_key_exists('_group', $limits) &&
            count($limits['_group']) > 0
        ) {
            $plugin_state = [];

            foreach($limits['_group'] as $group => $group_value) {
                foreach($group_value as $host => $host_value) {
                    foreach($host_value as $plugin => $plugin_value) {
                        foreach($plugin_value as $datasource => $datasource_value) {
                            if(array_key_exists('state', $datasource_value)) {
                                $state = $datasource_value['state'];

                                if($state == 'ok') {
                                    continue;
                                }
                                elseif(
                                        array_key_exists('_group', $data) &&
                                        array_key_exists($group, $data['_group']) &&
                                        array_key_exists($host, $data['_group'][$group]) &&
                                        array_key_exists('_plugin', $data['_group'][$group][$host]) &&
                                        array_key_exists($plugin, $data['_group'][$group][$host]['_plugin']) &&
                                        array_key_exists($datasource.'.graph', $data['_group'][$group][$host]['_plugin'][$plugin]) &&
                                        $data['_group'][$group][$host]['_plugin'][$plugin][$datasource.'.graph'] == 'no'
                                ) {
                                    continue;
                                }

                                if(!array_key_exists($plugin, $plugin_state)) {
                                    $plugin_state[$plugin] = [];
                                }
                                if(!array_key_exists($state, $plugin_state[$plugin])) {
                                    $plugin_state[$plugin][$state] = 0;
                                }

                                $plugin_state[$plugin][$state] += 1;
                            }
                        }
                    }
                }
            }

            foreach($plugin_state as $plugin => $plugin_value) {
                foreach($problem_count as $key => $value) {
                    if(array_key_exists($key, $plugin_value)) {
                        $problem_count[$key]++;
                    }
                }
            }
        }

        return $problem_count;
    }

    public static function getProblemHosts($limits, $data)
    {
        $problem_hosts = [];

        if(
            array_key_exists('_group', $limits) &&
            count($limits['_group']) > 0
        ) {
            foreach($limits['_group'] as $group => $group_value) {
                foreach($group_value as $host => $host_value) {
                    foreach($host_value as $plugin => $plugin_value) {
                        foreach($plugin_value as $datasource => $datasource_value) {
                            if(array_key_exists('state', $datasource_value)) {
                                $state = $datasource_value['state'];

                                if($state == 'ok') {
                                    continue;
                                }

                                if(!array_key_exists($state, $problem_hosts)) {
                                    $problem_hosts[$state] = [];
                                }
                                if(!array_key_exists($group, $problem_hosts[$state])) {
                                    $problem_hosts[$state][$group] = [];
                                }
                                if(!array_key_exists($host, $problem_hosts[$state][$group])) {
                                    $problem_hosts[$state][$group][$host] = [];
                                }

                                if(!in_array($plugin, $problem_hosts[$state][$group][$host])) {
                                    array_push($problem_hosts[$state][$group][$host], $plugin);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $problem_hosts;
    }

    public static function getHostProblems($limits, $data)
    {
        $host_problems = [];

        if(
            array_key_exists('_group', $limits) &&
            count($limits['_group']) > 0
        ) {
            foreach($limits['_group'] as $group => $group_value) {
                foreach($group_value as $host => $host_value) {
                    foreach($host_value as $plugin => $plugin_value) {
                        foreach($plugin_value as $datasource => $datasource_value) {
                            if(array_key_exists('state', $datasource_value)) {
                                $state = $datasource_value['state'];

                                if($state == 'ok') {
                                    continue;
                                }
                                elseif(
                                        array_key_exists('_group', $data) &&
                                        array_key_exists($group, $data['_group']) &&
                                        array_key_exists($host, $data['_group'][$group]) &&
                                        array_key_exists('_plugin', $data['_group'][$group][$host]) &&
                                        array_key_exists($plugin, $data['_group'][$group][$host]['_plugin']) &&
                                        array_key_exists($datasource.'.graph', $data['_group'][$group][$host]['_plugin'][$plugin]) &&
                                        $data['_group'][$group][$host]['_plugin'][$plugin][$datasource.'.graph'] == 'no'
                                ) {
                                    continue;
                                }

                                if(!array_key_exists($group, $host_problems)) {
                                    $host_problems[$group] = [];
                                }
                                if(!array_key_exists($host, $host_problems[$group])) {
                                    $host_problems[$group][$host] = [];
                                }
                                if(!array_key_exists($plugin, $host_problems[$group][$host])) {
                                    $host_problems[$group][$host][$plugin] = [];
                                }
                                if(!array_key_exists($state, $host_problems[$group][$host][$plugin])) {
                                    $host_problems[$group][$host][$plugin][$state] = [];
                                }
                                if(!array_key_exists($datasource, $host_problems[$group][$host][$plugin][$state])) {
                                    $host_problems[$group][$host][$plugin][$state][$datasource] = $datasource_value;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $host_problems;
    }

    public static function getCategoryProblems($limits, $data)
    {
        $category_problems = [];

        if(
            array_key_exists('_group', $limits) &&
            count($limits['_group']) > 0
        ) {
            foreach($limits['_group'] as $group => $group_value) {
                foreach($group_value as $host => $host_value) {
                    foreach($host_value as $plugin => $plugin_value) {
                        foreach($plugin_value as $datasource => $datasource_value) {
                            if(array_key_exists('state', $datasource_value)) {
                                $state = $datasource_value['state'];

                                if($state == 'ok') {
                                    continue;
                                }
                                elseif(
                                        array_key_exists('_group', $data) &&
                                        array_key_exists($group, $data['_group']) &&
                                        array_key_exists($host, $data['_group'][$group]) &&
                                        array_key_exists('_plugin', $data['_group'][$group][$host]) &&
                                        array_key_exists($plugin, $data['_group'][$group][$host]['_plugin']) &&
                                        array_key_exists($datasource.'.graph', $data['_group'][$group][$host]['_plugin'][$plugin]) &&
                                        $data['_group'][$group][$host]['_plugin'][$plugin][$datasource.'.graph'] == 'no'
                                ) {
                                    continue;
                                }

                                if(
                                    array_key_exists('_group', $data) &&
                                    array_key_exists($group, $data['_group']) &&
                                    array_key_exists($host, $data['_group'][$group]) &&
                                    array_key_exists('_plugin', $data['_group'][$group][$host]) &&
                                    array_key_exists($plugin, $data['_group'][$group][$host]['_plugin']) &&
                                    count($data['_group'][$group][$host]['_plugin']) > 0
                                ) {
                                    $category = 'other';
                                    if(array_key_exists('graph_category', $data['_group'][$group][$host]['_plugin'][$plugin])) {
                                        $category = mb_strtolower($data['_group'][$group][$host]['_plugin'][$plugin]['graph_category']);
                                    }

                                    if(!array_key_exists($group, $category_problems)) {
                                        $category_problems[$group] = [];
                                    }
                                    if(!array_key_exists($host, $category_problems[$group])) {
                                        $category_problems[$group][$host] = [];
                                    }
                                    if(!array_key_exists($category, $category_problems[$group][$host])) {
                                        $category_problems[$group][$host][$category] = [];
                                    }
                                    if(!array_key_exists($state, $category_problems[$group][$host][$category])) {
                                        $category_problems[$group][$host][$category][$state] = [];
                                    }
                                    if(!array_key_exists($plugin, $category_problems[$group][$host][$category][$state])) {
                                        $category_problems[$group][$host][$category][$state][$plugin] = [];
                                    }
                                    if(!array_key_exists($datasource, $category_problems[$group][$host][$category][$state][$plugin])) {
                                        $category_problems[$group][$host][$category][$state][$plugin][$datasource] = $datasource_value;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $category_problems;
    }
}

?>
