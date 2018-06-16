<?php

namespace DPX\SwooleServerBundle\Swoole;

use DPX\SwooleServerBundle\Exception\SwooleException;
use Swoole\Process;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Server
 *
 * @package \DPX\SwooleServerBundle\Swoole
 */
class Server
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var array
     */
    private $options;

    /**
     * @var \Swoole\Http\Server
     */
    private $server;

    public function __construct(string $host, int $port, array $options, KernelInterface $kernel)
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
        $this->kernel = $kernel;
    }

    /**
     * Get swoole configuration option value.
     *
     * @param string $key
     * @return mixed
     */
    private function getOption(string $key)
    {
        $option = $this->options[$key];

        if (!$option) {
            throw new \InvalidArgumentException(sprintf("Parameter not found: %s", $key));
        }

        return $option;
    }

    /**
     * @return int
     * @throws SwooleException
     */
    public function getPid()
    {
        $file = $this->getPidFile();

        if (!file_exists($file)) {
            throw new SwooleException("The pid file not found.");
        }

        $pid = (int) file_get_contents($file);

        if (!$pid) {
            $this->removePidFile();

            return 0;
        }

        return $pid;
    }

    /**
     * Get pid file.
     *
     * @return string
     */
    public function getPidFile()
    {
        return $this->getOption('pid_file');
    }

    /**
     * Remove the pid file.
     */
    private function removePidFile()
    {
        $file = $this->getPidFile();

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Start and configure swoole server.
     */
    public function start(callable $cb)
    {
        $this->createServer();
        $this->configureSwooleServer();
        $this->symfonyBridge($cb);
    }

    /**
     * Create the swoole http server.
     */
    private function createServer()
    {
        $this->server = new \Swoole\Http\Server($this->host, $this->port);
    }

    /**
     * Configure the created server.
     */
    private function configureSwooleServer()
    {
        $this->server->set($this->options);
    }

    /**
     * @param callable $cb
     */
    private function symfonyBridge(callable $cb)
    {
        $this->server->on('start', function () use ($cb) {
            $cb('Server started!');
        });

        $callback = function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $symfonyResponse = $this->kernel->handle(Request::toSymfony($request));

            Response::toSwoole($response, $symfonyResponse);
        };

        $this->server->on('request', $callback);

        $this->server->start();
    }

    /**
     * Stop the swoole server.
     *
     * @throws \Exception
     */
    public function stop()
    {
        $kill = Process::kill($this->getPid());

        if (!$kill) {
            throw new SwooleException("Swoole server not stopped!");
        }

        return $kill;
    }

    /**
     * Reload swoole server.
     *
     * @throws \Exception
     */
    public function reload()
    {
        $reload = Process::kill($this->getPid(), SIGUSR1);

        if (!$reload) {
            throw new SwooleException("Swoole server not reloaded!");
        }

        return $reload;
    }
}