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
        'simplecas' => 'simplecas.xml',
    );

    /**
     * Load the SimpleCAS configuration.
     *
     * @param array                $config        A configuration array
     * @param BuilderConfiguration $configuration A BuilderConfiguration instance
     */
    public function simplecasLoad($config, BuilderConfiguration $configuration)
    {
        if (!$configuration->hasDefinition('simplecas')) {
            $loader = new XmlFileLoader(__DIR__.'/../Resources/config');
            $configuration->merge($loader->load($this->resources['simplecas']));
        }

        if (isset($config['adapter']['name'])) {
            $configuration->setAlias('simplecas.adapter', 'simplecas.adapter.'.$config['adapter']['name']);

            if (isset($config['adapter']['options'])) {
                $configuration->setParameter('simplecas.adapter.options', $config['adapter']['options']);
            }
        }

        foreach (array('hostname', 'uri', 'logout_service_redirect') as $key) {
            if (isset($config['protocol'][$key])) {
                $configuration->setParameter('simplecas.protocol.'.$key, $config['protocol'][$key]);
            }
        }

        if (isset($config['protocol']['request'])) {
            foreach (array('method', 'config') as $key) {
                if (isset($config['protocol']['request'][$key])) {
                    $configuration->setParameter('simplecas.protocol.request.'.$key, $config['protocol']['request'][$key]);
                }
            }
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
