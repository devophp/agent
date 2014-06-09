<?php 

namespace Devophp\Component\Agent\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use FuseSource\Stomp\Stomp;
use FuseSource\Stomp\Frame;
use FuseSource\Stomp\Exception\StompException;

class SendCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('agent:send')
            ->setDescription(
                'Send cmd with arg'
            )
            ->addArgument(
                'cmd',
                InputArgument::REQUIRED,
                'command'
            )
            ->addOption(
                'parametersjson',
                null,
                InputOption::VALUE_OPTIONAL,
                'parameters as JSON'
            );
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('cmd');
        $parameterjson = $input->getOption('parametersjson');
        if ($parameterjson) {
            $parameters = json_decode($parameterjson);
        }
        
        $destination = '/queue/' . 'devophp/' . 'agents';
        $output->write("Sending command '$command' with parameters '" . json_encode($parameters) . " to '$destination'\n");

        $properties = array('selector' => "username='" . $username . "'");
        $properties = null;
        
        $servername = 'localhost';
        $portnumber = 61613;
        try {
            $stomp = new Stomp('tcp://' . $servername . ':' . $portnumber);
            $stomp->clientId = "testing.1.2.3.sender";
            $stomp->connect();
            $output->writeln('<info>Connected to "' . $servername . ':' . $portnumber . '"</info>');
        } catch (StompException $e) {
            $output->writeln('<error>Connection failed: ' . $e->getMessage() . '</error>');
            exit($e->getCode());
        }
        $message = array();
        $message['command'] = $command;
        $message['parameters'] = $parameters;
        $messagejson = json_encode($message);
        $output->writeln("Sending message: " . $messagejson);
        $stomp->send($destination, $messagejson);
    }
}
