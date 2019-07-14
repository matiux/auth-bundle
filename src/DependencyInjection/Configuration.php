<?php

namespace GabyQuiles\Auth\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('gaby_quiles_auth_jws');
        $rootNode = \method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('gabyquiles_jwt_auth_extensions');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('token_ttl')
            ->defaultValue(3600)
            ->end()
            ->scalarNode('clock_skew')
            ->defaultValue(0)
            ->end()
            ->end();
        return $treeBuilder;
    }
}