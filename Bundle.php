<?php

namespace Bundle\SimpleCASBundle;

use Symfony\Foundation\Bundle\Bundle as BaseBundle;
use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\Loader\Loader;
use Bundle\SimpleCASBundle\DependencyInjection\SimpleCASExtension;

/**
 * Bundle.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Bundle extends BaseBundle
{
	/**
	 * Customizes the Container instance.
	 *
	 * @param Symfony\Components\DependencyInjection\ContainerInterface $container A ContainerInterface instance
	 * @return Symfony\Components\DependencyInjection\BuilderConfiguration A BuilderConfiguration instance
	 */
	public function buildContainer(ContainerInterface $container)
	{
		Loader::registerExtension(new SimpleCASExtension());
	}
}
