Installation
============


Open a command console, enter your project directory and execute:

```console
$ composer require emnsen/swoole-server-bundle
```

USAGE
----------------------------------

```bash
# Start the swoole server
$ php bin/console swoole:server:start
```

```bash
# Stop the swoole server
$ php bin/console swoole:server:stop
```

```bash
# Reload the swoole server
$ php bin/console swoole:server:reload
```

Configuration
----------------------------------

### Default Configs
```yaml
host: 0.0.0.0
port: 8080
options:
    pid_file: /var/run/swoole_server.pid
    log_file: %kernel.logs_dir%/swoole.log
    daemonize: true
    document_root: %kernel.project_dir%/public
    enable_static_handler: true
```

### Other Configs
*Note: these options have not been tried*

```yaml
options:
    max_request: ~
    open_cpu_affinity: ~
    task_worker_num: ~
    enable_port_reuse: ~
    worker_num: ~
    reactor_num: ~
    dispatch_mode: ~
    discard_timeout_request: ~
    open_tcp_nodelay: ~
    open_mqtt_protocol: ~
    user: ~
    group: ~
    ssl_cert_file: ~
    ssl_key_file: ~
```