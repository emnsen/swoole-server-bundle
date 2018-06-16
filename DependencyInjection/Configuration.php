<?php

namespace DPX\SwoolerServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package \App\SwooleBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('swoole_server', 'array');

        $rootNode
            ->children()
                ->scalarNode('host')->defaultValue('0.0.0.0')->end()
                ->integerNode('port')->defaultValue(3000)->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pid_file')
                            ->cannotBeEmpty()
                            ->defaultValue('/var/run/swoole_server.pid')
                        ->end()
                        ->scalarNode('log_file')
                            ->cannotBeEmpty()
                            ->defaultValue('%kernel.logs_dir%/swoole.log')
                        ->end()
                        ->booleanNode('daemonize')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('document_root')
                            ->cannotBeEmpty()
                            ->defaultValue('%kernel.project_dir%/public')
                        ->end()
                        ->booleanNode('enable_static_handler')
                            ->defaultTrue()
                        ->end()
                        ->variableNode('max_request')->end()
                        ->variableNode('open_cpu_affinity')->end()
                        ->variableNode('task_worker_num')->end()
                        ->variableNode('enable_port_reuse')->end()
                        ->variableNode('worker_num')->end()
                        ->variableNode('reactor_num')->end()
                        ->variableNode('dispatch_mode')->end()
                        ->variableNode('discard_timeout_request')->end()
                        ->variableNode('open_tcp_nodelay')->end()
                        ->variableNode('open_mqtt_protocol')->end()
                        ->variableNode('user')->end()
                        ->variableNode('group')->end()
                        ->variableNode('ssl_cert_file')->end()
                        ->variableNode('ssl_key_file')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
