<?php

namespace Bundle\SimpleCASBundle\DependencyInjection;

use Symfony\Components\DependencyInjection\Loader\LoaderExtension;
use Symfony\Components\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Components\DependencyInjection\BuilderConfiguration;

/**
 * SimpleCASExtension is an extension for the SimpleCAS library.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASExtension extends LoaderExtension
{
    protected $resources = array(
        'adapter' => 'adapter.xml',
        'client'  => 'client.xml',
        'helper'  => 'helper.xml',
    );

    /**
     * Load the SimpleCAS adapter configuration.
     *
     * @param array                $config        A configuration array
     * @param BuilderConfiguration $configuration A BuilderConfiguration instance
     */
    public function adapterLoad($config, BuilderConfiguration $configuration)
    {
        if (!$configuration->hasDefinition('adapter')) {
            $loader = new XmlFileLoader(__DIR__.'/../Resources/config');
            $configuration->merge($loader->load($this->resources['adapter']));
        }

        if (isset($config['name'])) {
            $configuration->setAlias('simplecas.adapter', 'simplecas.adapter.'.$config['name']);

            if (isset($config['options'])) {
                $configuration->setParameter('simplecas.adapter.options', $config['options']);
            }
        }

        return $configuration;
    }

    /**
     * Load the SimpleCAS client configuration.
     *
     * @param array                $config        A configuration array
     * @param BuilderConfiguration $configuration A BuilderConfiguration instance
     */
    public function clientLoad($config, BuilderConfiguration $configuration)
    {
        if (!$configuration->hasDefinition('client')) {
            $loader = new XmlFileLoader(__DIR__.'/../Resources/config');
            $configuration->merge($loader->load($this->resources['client']));
        }

        foreach (array('hostname', 'uri', 'logout_service_redirect') as $key) {
            if (isset($config[$key])) {
                $configuration->setParameter('simplecas.protocol.'.$key, $config[$key]);
            }
        }

        if (isset($config['request'])) {
            foreach (array('method', 'config') as $key) {
                if (isset($config['request'][$key])) {
                    $configuration->setParameter('simplecas.protocol.request.'.$key, $config['request'][$key]);
                }
            }
        }

        return $configuration;
    }

    /**
     * Load the SimpleCAS templating helper configuration.
     *
     * @param array                $config        A configuration array
     * @param BuilderConfiguration $configuration A BuilderConfiguration instance
     */
    public function helperLoad($config, BuilderConfiguration $configuration)
    {
        if (!$configuration->hasDefinition('helper')) {
            $loader = new XmlFileLoader(__DIR__.'/../Resources/config');
            $configuration->merge($loader->load($this->resources['helper']));
        }

        return $configuration;
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
