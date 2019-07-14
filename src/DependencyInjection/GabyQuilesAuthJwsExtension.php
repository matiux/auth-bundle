<?php

namespace GabyQuiles\Auth\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GabyQuilesAuthJwsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
//        TODO: Add check for class
        $loader->load('services.yaml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $keyLoaderDefinition = $container->getDefinition('gabyquiles_jwt_auth_extensions.jwk_key_loader');
        $definition = $container->getDefinition('gabyquiles_jwt_auth_extensions.aws_jwt_provider');
//        $definition->replaceArgument('$keyLoader', $keyLoaderDefinition);
        $container->setParameter('gaby_quiles_auth_jws.token_ttl', $config['token_ttl']);
        $container->setParameter('gaby_quiles_auth_jws.clock_skew', $config['clock_skew']);
    }
}