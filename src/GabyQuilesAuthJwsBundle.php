<?php

namespace GabyQuiles\Auth;

use GabyQuiles\Auth\DependencyInjection\CompilerPasses\ConfigureJwkFetcher;
use GabyQuiles\Auth\DependencyInjection\CompilerPasses\ConfigureLcobucciEncoderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GabyQuilesAuthJwsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigureLcobucciEncoderCompilerPass());
        $container->addCompilerPass(new ConfigureJwkFetcher());
    }
}