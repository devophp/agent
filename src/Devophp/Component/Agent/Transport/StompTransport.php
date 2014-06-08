<?php

namespace Devophp\Component\Agent\Transport;

use Devophp\Component\Agent\Message;
use FuseSource\Stomp\Stomp;
use FuseSource\Stomp\Frame;
use FuseSource\Stomp\Exception\StompException;

use Monolog\Logger;
use RuntimeException;

class StompTransport implements TransportInterface
{
    private $logger;
    public function init(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    private $server;
    private $port;
    private $stomp;
    
    public function __construct($server, $port)
    {
        $this->server = $server;
        $this->port = $port;
    }
    
    public function connect()
    {
        try {
            $this->stomp = new Stomp('tcp://' . $this->server . ':' . $this->port);
            $this->stomp->clientId = "testing.1.2.3";
            $this->stomp->connect();
            $this->logger->info('Connected to "' . $this->server . ':' . $this->port . '"');
        } catch (StompException $e) {
            $this->logger->warn('Connection failed: ' . $e->getMessage());
            throw new RuntimeException("Connection failed");
        }
        
        $this->stomp->setReadTimeout(5, 0);
        
        //$properties = array('selector' => "username='" . $username . "'");
        $properties = null;

        $destination = '/queue/' . 'prefix/' . 'test';
        $this->stomp->subscribe($destination, $properties);
        
    }
    
    public function getMessage()
    {
        $msg = $this->stomp->readFrame();
        if ($msg!=null) {
            $this->logger->debug('Received message: ' . $msg->body . '(' . $msg->headers['message-id'] . '/' . $msg->headers['destination'] . ')');
            $this->stomp->ack($msg);
            $message = new Message($msg->body);
            return $message;
        }
        return null;

    }
    
    public function sendMessage($data)
    {
        $messagejson = json_encode($data);
        $this->stomp->send('/queue/server/test', $messagejson);
    }
}
