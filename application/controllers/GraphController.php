<?php

namespace Icinga\Module\Munin\Controllers;

use Icinga\Web\Controller;

use Icinga\Module\Munin\CustomPages;
use Icinga\Module\Munin\Datafile;
use Icinga\Module\Munin\Limits;
use Icinga\Module\Munin\Periods;

class GraphController extends Controller
{
    public function showAction()
    {
        $this->setAutorefreshInterval(300);

        $group    = $this->params->get('group');
        $host     = $this->params->get('host');
        $plugin   = $this->params->get('plugin');

        $category = $this->params->get('category');
        $period   = $this->params->get('period');

        $debug    = $this->params->get('debug');

        $this->view->group    = $group;
        $this->view->host     = $host;
        $this->view->plugin   = $plugin;

        $this->view->category = $category;
        $this->view->period   = $period;

        $this->view->debug    = $debug;


        $config = $this->Config();
        $this->view->config = $config;

        $graph_strategy = $this->config->get('global', 'graph_strategy', 'cron');
        $this->view->graph_strategy = $graph_strategy;

        $munin_baseurl = $this->config->get('global', 'baseurl', '/munin');
        $this->view->munin_baseurl = $munin_baseurl;

        $cgiurl_graph = $this->config->get('global', 'cgiurl_graph', '/munin-cgi/munin-cgi-graph');
        $this->view->cgiurl_graph = $cgiurl_graph;

        if ($graph_strategy == 'cgi') {
            $this->view->graph_baseurl = $cgiurl_graph;
        } else {
            $this->view->graph_baseurl = $munin_baseurl;
        }


        $config_file = $this->config->get('custom_pages', 'config_file');
        $custom_pages = CustomPages::parseConfig($config_file);
        $this->view->custom_pages = $custom_pages;


        $datafile_path = $config->get('global', 'datafile_path', '/var/lib/munin/datafile');
        $data = Datafile::parseFile($datafile_path);
        $this->view->data = $data;

        $groups = Datafile::getGroups($data);
        $this->view->groups = $groups;

        $categories = Datafile::getCategories($data);
        $this->view->categories = $categories;

        $group_categories = Datafile::getGroupCategories($data);
        $this->view->group_categories = $group_categories;

        $host_categories = Datafile::getHostCategories($data);
        $this->view->host_categories = $host_categories;


        $limits_path = $config->get('global', 'limits_path', '/var/lib/munin/limits');
        $limits = Limits::parseFile($limits_path);
        $this->view->limits = $limits;

        $problems = Limits::getProblems();
        $this->view->problems = $problems;

        $problem_title = Limits::getProblemTitle();
        $this->view->problem_title = $problem_title;

        $problem_count = Limits::getProblemCount($limits, $data);
        $this->view->problem_count = $problem_count;

        $problem_hosts = Limits::getProblemHosts($limits, $data);
        $this->view->problem_hosts = $problem_hosts;

        $host_problems = Limits::getHostProblems($limits, $data);
        $this->view->host_problems = $host_problems;

        $category_problems = Limits::getCategoryProblems($limits, $data);
        $this->view->category_problems = $category_problems;


        $periods = Periods::getPeriods();
        $this->view->periods = $periods;

        $period_days = Periods::getPeriodDays();
        $this->view->period_days = $period_days;

        $periodicity = Periods::getPeriodicity();
        $this->view->periodicity = $periodicity;


        $title = "Munin";
        if ($group && in_array($group, $groups)) {
            $title = "$group :: $title";

            if ($host &&
                array_key_exists('_group', $data) &&
                array_key_exists($group, $data['_group']) &&
                array_key_exists($host, $data['_group'][$group])
            ) {
                $title = "$host :: $title";

                if ($plugin &&
                    array_key_exists('_plugin', $data['_group'][$group][$host]) &&
                    array_key_exists($plugin, $data['_group'][$group][$host]['_plugin'])
                ) {
                    $title = "$plugin :: $title";
                }
            }
        } elseif ($category &&
                  in_array($category, $categories)
                 ) {
            $title = "$category :: $title";
        }
        $this->view->title = $title;
    }

    public function problemsAction()
    {
        $this->showAction();

        $title = "Problems :: Munin";
        $this->view->title = $title;
    }

    public function customAction()
    {
        $this->showAction();

        $page = $this->params->get('page');
        $this->view->page = $page;

        $title = "Munin";
        if (count($this->view->custom_pages) > 0 &&
            array_key_exists($page, $this->view->custom_pages) &&
            array_key_exists('title', $this->view->custom_pages[$page])
        ) {
            $custom_title = $this->view->custom_pages[$page]['title'];

            $title = "$custom_title :: $title";
        } else {
            $title = "Custom Page :: $title";
        }
        $this->view->title = $title;
    }
}
