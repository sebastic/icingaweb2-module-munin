<?php

namespace Icinga\Module\Munin;

use Icinga\Application\Logger;

class Datafile {
    public static function parseFile($file) {
        $data = [];

        if(is_file($file) && is_readable($file)) {
            $content = file_get_contents($file);

            if($content === FALSE) {
                Logger::error("Failed to read datafile: $file");
            }
            else {
                foreach(preg_split('/\r?\n/', $content) as $line) {
                    # version 2.0.33-1
                    if(preg_match('/^(version)\s+(\S+.*?\S+)\s*$/', $line, $matches)) {
                        $key   = $matches[1];
                        $value = $matches[2];

                        $data[$key] = $value;
                    }
                    # example.com;host.example.com:load.graph_title Load average
                    elseif(preg_match('/^(\S+?);(\S+):(\S+?)\.(\S+)\s+(\S+.*?)\s*$/', $line, $matches)) {
                        $group  = $matches[1];
                        $host   = $matches[2];
                        $plugin = $matches[3];
                        $key    = $matches[4];
                        $value  = $matches[5];

                        $data['_group'][$group][$host]['_plugin'][$plugin][$key] = $value;

                        if(!array_key_exists('graph_category', $data['_group'][$group][$host]['_plugin'][$plugin])) {
                            $data['_group'][$group][$host]['_plugin'][$plugin]['graph_category'] = 'other';
                        }
                    }
                    else {
                        Logger::warning("Cannot parse line: $line");
                    }
                }

                if(array_key_exists('_group', $data)) {
                    foreach($data['_group'] as $group => $group_value) {
                        foreach($group_value as $host => $host_value) {
                            foreach($host_value as $_plugin => $_plugin_value) {
                                foreach($_plugin_value as $plugin => $plugin_value) {
                                    if(array_key_exists('graph_category', $plugin_value)) {
                                        $category = mb_strtolower($plugin_value['graph_category']);

                                        $data['_group'][$group][$host]['_category'][$category][$plugin] = $data['_group'][$group][$host]['_plugin'][$plugin];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        else {
            Logger::error("Cannot read datafile: $file");
        }

        return $data;
    }

    public static function getGroups($data) {
        $groups = [];

        if(
            array_key_exists('_group', $data) &&
            count($data['_group']) > 0
        ) {
            $groups = array_keys($data['_group']);

            sort($groups);
        }

        return $groups;
    }

    public static function getCategories($data) {
        $categories = [];

        if(
            array_key_exists('_group', $data) &&
            count($data['_group']) > 0
        ) {
            foreach($data['_group'] as $group => $group_value) {
                foreach($group_value as $host => $host_value) {
                    if(
                        array_key_exists('_category', $host_value) &&
                        count($host_value['_category']) > 0
                    ) {
                        foreach($host_value['_category'] as $category => $category_value)  {
                            if(!in_array($category, $categories)) {
                                array_push($categories, $category);
                            }
                        }
                    }
                }
            }

            sort($categories);
        }

        return $categories;
    }

    public static function getGroupCategories($data) {
        $group_categories = [];

        if(
            array_key_exists('_group', $data) &&
            count($data['_group']) > 0
        ) {
            foreach($data['_group'] as $group => $group_value) {
                $categories = [];

                foreach($data['_group'][$group] as $host => $host_value) {
                    if(array_key_exists('_category', $host_value)) {
                        foreach($host_value['_category'] as $category => $category_value) {
                            if(!in_array($category, $categories)) {
                                array_push($categories, $category);
                            }
                        }
                    }
                }

                sort($categories);

                $group_categories[$group] = $categories;
            }

            ksort($group_categories);
        }

        return $group_categories;
    }

    public static function getHostCategories($data) {
        $host_categories = [];

        if(
            array_key_exists('_group', $data) &&
            count($data['_group']) > 0
        ) {
            foreach($data['_group'] as $group => $group_value) {
                foreach($data['_group'][$group] as $host => $host_value) {
                    $categories = [];

                    if(array_key_exists('_category', $host_value)) {
                        foreach($host_value['_category'] as $category => $category_value) {
                            if(!in_array($category, $categories)) {
                                array_push($categories, $category);
                            }
                        }
                    }

                    sort($categories);

                    if(!array_key_exists($group, $host_categories)) {
                        $host_categories[$group] = [];
                    }

                    $host_categories[$group][$host] = $categories;
                }

                ksort($host_categories[$group]);
            }

            ksort($host_categories);
        }

        return $host_categories;
    }
}

?>
