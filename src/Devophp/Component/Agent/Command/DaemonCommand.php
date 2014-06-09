<?php 

namespace Devophp\Component\Agent\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Devophp\Component\Agent\Transport\StompTransport;

class DaemonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('agent:daemon')
            ->setDescription(
                'Run daemon'
            );
    }
    
    private $plugin = array();
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        
        $output->write("Starting Devophp Agent");
        
        $logger = new Logger('devophp-agent');
        $logger->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));
        
        $servername = 'localhost';
        $portnumber = 61613;

        $transport = new StompTransport($servername, $portnumber);
        $transport->init($logger);
        $transport->connect();
        
        $message = array();
        $hostname = gethostname();
        
        $message['command'] = 'monitor:register';
        $message['from'] = $hostname;
        
        $parameters['hostname'] = $hostname;
        $parameters['os'] = 'darwin';
        $message['parameters'] = $parameters;
        
        $transport->sendMessage($message, '/queue/devophp/monitor');
        
        // setup plugins
        $nagiosplugin = new \Devophp\Component\Agent\Plugin\NagiosPlugin();
        $nagiosplugin->init($logger, $transport);
        $this->plugin['nagios'] = $nagiosplugin;

        $sleepInterval = 1;
        
        while (true) {
            $logger->debug("Listening for messages");
            $message = $transport->getMessage();
            if ($message != null) {
                $this->handleMessage($message);
            } else {
                $logger->debug("Message queue empty");
                sleep($sleepInterval);
            }
        }
    }
    
    private function handleMessage($message)
    {
        $data = json_decode($message->getBody(), true);
        $command=$data['command'];
        $parameters=$data['parameters'];
        switch($command) {
            case "nagios:check":
                $res = $this->plugin['nagios']->check($parameters);
                break;
            default:
                exit('Unsupported command: ' . $command);
                break;
        }
    }
}
