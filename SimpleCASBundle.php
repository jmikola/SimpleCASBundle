<?php

namespace Bundle\SimpleCASBundle;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;
use Bundle\SimpleCASBundle\DependencyInjection\SimpleCASExtension;

/**
 * SimpleCAS Bundle.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASBundle extends \Symfony\Framework\Bundle\Bundle
{
    /**
     * Customizes the Container instance.
     *
     * @param Symfony\Components\DependencyInjection\ContainerInterface $container A ContainerInterface instance
     * @return Symfony\Components\DependencyInjection\BuilderConfiguration A BuilderConfiguration instance
     */
    public function buildContainer(ParameterBagInterface $parameterBag)
    {
        ContainerBuilder::registerExtension(new SimpleCASExtension());
    }
}
