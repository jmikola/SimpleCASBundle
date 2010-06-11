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
     * @param array $config A configuration array
     * @return BuilderConfiguration A BuilderConfiguration instance
     */
    public function simplecasLoad($config)
    {
        $configuration = new BuilderConfiguration();

        $loader = new XmlFileLoader(__DIR__.'/../Resources/config');
        $configuration->merge($loader->load($this->resources['simplecas']));

        foreach (array('login_redirect_route', 'login_redirect_url') as $key) {
            if (isset($config[$key])) {
                $configuration->setParameter('simplecas.'.$key, $config[$key]);
            }
        }

        foreach (array('hostname', 'uri') as $key) {
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
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    public function getAlias()
    {
        return 'simplecas';
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     */
    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/simplecas';
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
}
