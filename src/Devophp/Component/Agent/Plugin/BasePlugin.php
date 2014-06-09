<?php

namespace Devophp\Component\Agent\Plugin;

use Monolog\Logger;
use Devophp\Component\Agent\Transport\TransportInterface;

abstract class BasePlugin
{
    
    private $logger;
    private $transport;
    
    public function init(Logger $logger, TransportInterface $transport)
    {
        $this->logger = $logger;
        $this->transport = $transport;
    }

    public function sendMessage($data, $queue)
    {
        $this->transport->sendMessage($data, $queue);
    }

    public function warning($message, $context = array())
    {
        $this->logger->warning($message, $context);
    }

    public function info($message, $context  = array())
    {
        $this->logger->info($message, $context);
    }
}
