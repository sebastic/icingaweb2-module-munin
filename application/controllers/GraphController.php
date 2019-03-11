<?php

namespace Icinga\Module\Munin\Controllers;

use Icinga\Web\Controller;

use Icinga\Module\Munin\Datafile;
use Icinga\Module\Munin\Limits;
use Icinga\Module\Munin\Periods;

class GraphController extends Controller
{
    public function showAction()
    {
        $this->view->group      = $this->params->get('group');
        $this->view->host       = $this->params->get('host');
        $this->view->plugin     = $this->params->get('plugin');

        $this->view->category   = $this->params->get('category');
        $this->view->period     = $this->params->get('period');

        $this->view->debug      = $this->params->get('debug');


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
    }

    public function problemsAction()
    {
        $this->showAction();
    }
}
