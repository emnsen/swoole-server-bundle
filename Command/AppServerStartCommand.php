<?php

namespace DPX\SwooleServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AppServerCommand
 *
 * @package \App\Command
 */
class AppServerStartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('swoole:server:start')
            ->setDescription('Start Swoole HTTP Server.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $server = $this->getContainer()->get('app.swoole.server');

        if ($server->isRunning()) {
            $io->warning('Server is running! Please before stop the server.');
        } else {
            $cb = function($m) use ($io) {
                $io->success($m);
            };

            $server->start($cb);
        }
    }
}