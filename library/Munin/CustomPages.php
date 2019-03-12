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
                    $custom_pages = $data;
                }
            }
        } else {
            Logger::error("Cannot read config file: $file");
        }

        return $custom_pages;
    }
}
