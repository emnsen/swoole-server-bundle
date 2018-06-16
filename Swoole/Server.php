<?php

declare(strict_types=1);

namespace DPX\SwooleServerBundle\Swoole;

use DPX\SwooleServerBundle\Exception\SwooleException;
use Swoole\Process;
use Symfony\Component\HttpKernel\KernelInterface;

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

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Server constructor.
     * @param string $host
     * @param int $port
     * @param array $options
     * @param KernelInterface $kernel
     */
    public function __construct(string $host, int $port, array $options, KernelInterface $kernel)
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
        $this->kernel = $kernel;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get swoole configuration option value.
     *
     * @param string $key
     * @return mixed
     */
    public function getOption(string $key)
    {
        $option = $this->options[$key];

        if (!$option) {
            throw new \InvalidArgumentException(sprintf("Parameter not found: %s", $key));
        }

        return $option;
    }

    /**
     * @return int
     */
    private function getPid(): int
    {
        $file = $this->getPidFile();

        if (!file_exists($file)) {
            return 0;
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
    private function getPidFile(): string
    {
        return $this->getOption('pid_file');
    }

    /**
     * Remove the pid file.
     */
    private function removePidFile(): void
    {
        $file = $this->getPidFile();

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Start and configure swoole server.
     * @param callable $cb
     */
    public function start(callable $cb): void
    {
        $this->createServer();
        $this->configureSwooleServer();
        $this->symfonyBridge($cb);
    }

    /**
     * Create the swoole http server.
     */
    private function createServer(): void
    {
        $this->server = new \Swoole\Http\Server($this->host, $this->port);
    }

    /**
     * Configure the created server.
     */
    private function configureSwooleServer(): void
    {
        $this->server->set($this->options);
    }

    /**
     * @param callable $cb
     */
    private function symfonyBridge(callable $cb): void
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
     * @return bool
     * @throws SwooleException
     */
    public function stop(): bool
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
     * @return bool
     * @throws SwooleException
     */
    public function reload(): bool
    {
        $reload = Process::kill($this->getPid(), SIGUSR1);

        if (!$reload) {
            throw new SwooleException("Swoole server not reloaded!");
        }

        return $reload;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        $pid = $this->getPid();

        if (!$pid) {
            return false;
        }

        Process::kill($pid, 0);

        return !swoole_errno();
    }
}