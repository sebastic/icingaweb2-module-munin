<?php

namespace Icinga\Module\Munin;

use Icinga\Application\Logger;

class CustomPages
{
    public static function parseConfig($file)
    {
        $custom_pages = [];

        if (!$file) {
            return $custom_pages;
        }

        if (is_file($file) && is_readable($file)) {
            $content = file_get_contents($file);

            if ($content === false) {
                Logger::error("Failed to read config file: $file");
            } else {
                $data = json_decode($content, true);

                if ($data === null) {
                    Logger::error("Failed to parse config file: $file");
                } else {
                    $by_title = [];
                    foreach ($data as $key => $value) {
                        $title = 'N/A';
                        if (array_key_exists('title', $value)) {
                            $title = $value['title'];
                        }

                        $by_title[$title] = $key;
                    }

                    ksort($by_title);

                    foreach ($by_title as $title => $key) {
                        $custom_pages[$key] = $data[$key];
                    }
                }
            }
        } else {
            Logger::error("Cannot read config file: $file");
        }

        return $custom_pages;
    }
}
