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
        $checkdefinitionname = $parameters['checkdefinitionname'];
        
        $this->info("NagiosCheck:" . $check . "/" . $arguments);
        
        $response = $this->checker->check($check, $arguments);
        
        $this->info("ServiceOutput: " . $response->getServiceOutput() . " (code: " . $response->getStatusCode() . ")");
        
        $parameters = array();
        $parameters['statuscode'] = $response->getStatusCode();
        $parameters['statuslabel'] = $response->getStatusText();
        
        $parameters['serviceoutput'] = $response->getServiceOutput();
        $parameters['serviceperfdata'] = $response->getServicePerfData();
        $parameters['hostname'] = gethostname();
        $parameters['checkdefinitionname'] = $checkdefinitionname;
        
        $message = array();
        $message['command'] = "monitor:checkreport";
        $message['from'] = gethostname();
        $message['parameters'] = $parameters;
        
        $this->sendMessage($message, '/queue/devophp/monitor');
    }
}
