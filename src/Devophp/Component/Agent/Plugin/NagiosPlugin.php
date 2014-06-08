<?php

namespace Devophp\Component\Agent\Plugin;

use Devophp\Component\Nagios\Checker;
use Monolog\Logger;

class NagiosPlugin extends BasePlugin
{
    private $checker;
    private $logger;
    public function __construct()
    {
        $this->checker = new Checker();
        $this->checker->autoDetectPluginPath();
    }
    
    public function check($parameters)
    {
        $check = $parameters['check'];
        $arguments = $parameters['arguments'];
        
        $this->info("NagiosCheck:" . $check . "/" . $arguments);
        
        $response = $this->checker->check($check, $arguments);
        
        $this->info("ServiceOutput: " . $response->getServiceOutput() . " (code: " . $response->getStatusCode() . ")");
        
        $res = array();
        $res['statuscode'] = $response->getStatusCode();
        $res['statuslabel'] = $response->getStatusText();
        
        $res['serviceoutput'] = $response->getServiceOutput();
        $res['serviceperfdata'] = $response->getServicePerfData();
        
        $this->sendMessage($res);
    }
}
