<?php

namespace WG\OpenIdUserBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\DependencyInjection\ContainerBuilder;

class WGOpenIdUserExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
        $loader->load( 'services.yml' );
    }
}
