<?php

namespace Bundle\SimpleCASBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * SimpleCASExtension is an extension for the SimpleCAS library.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASExtension extends Extension
{
    protected $resources = array(
        'adapter' => 'adapter.xml',
        'client'  => 'client.xml',
        'helper'  => 'helper.xml',
    );

    /**
     * Load the SimpleCAS adapter configuration.
     *
     * @param array            $config    A configuration array
     * @param ContainerBuilder $container A BuilderConfiguration instance
     */
    public function adapterLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('adapter')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load($this->resources['adapter']);
        }

        if (isset($config['name'])) {
            $container->setAlias('simplecas.adapter', 'simplecas.adapter.'.$config['name']);

            if (isset($config['options'])) {
                $container->setParameter('simplecas.adapter.options', $config['options']);
            }
        }

        return $container;
    }

    /**
     * Load the SimpleCAS client configuration.
     *
     * @param array            $config    A configuration array
     * @param ContainerBuilder $container A BuilderConfiguration instance
     */
    public function clientLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('client')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load($this->resources['client']);
        }

        foreach (array('hostname', 'uri', 'logout_service_redirect') as $key) {
            if (isset($config[$key])) {
                $container->setParameter('simplecas.protocol.'.$key, $config[$key]);
            }
        }

        if (isset($config['request'])) {
            foreach (array('method', 'config') as $key) {
                if (isset($config['request'][$key])) {
                    $container->setParameter('simplecas.protocol.request.'.$key, $config['request'][$key]);
                }
            }
        }

        return $container;
    }

    /**
     * Load the SimpleCAS templating helper configuration.
     *
     * @param array            $config    A configuration array
     * @param ContainerBuilder $container A BuilderConfiguration instance
     */
    public function helperLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('helper')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load($this->resources['helper']);
        }

        return $container;
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/simplecas';
    }

    public function getAlias()
    {
        return 'simplecas';
    }
}
