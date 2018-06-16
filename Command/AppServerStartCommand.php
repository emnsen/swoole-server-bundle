<?php

namespace DPX\SwooleServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppServerStartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('swoole:server:start')
            ->setDescription('Start Swoole HTTP Server.')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'If your want to override the default configuration host, use these method.')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'If your want to override the default configuration port, use these method.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $server = $this->getContainer()->get('app.swoole.server');

            if ($server->isRunning()) {
                $io->warning('Server is running! Please before stop the server.');
            } else {

                $host = $input->getOption('host');
                if ($host) {
                    $server->setHost($host);
                }

                $port = $input->getOption('port');
                if ($port) {
                    $server->setPort($port);
                }

                $server->start(function (string $message) use ($io) {
                    $io->success($message);
                });
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }
    }
}