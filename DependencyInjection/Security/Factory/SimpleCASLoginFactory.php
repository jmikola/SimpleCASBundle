<?php

namespace Bundle\SimpleCASBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

/**
 * FormLoginFactory creates services for form login authentication.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASLoginFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $providerIds, $defaultEntryPoint)
    {
        $provider = 'security.authentication.provider.dao.'.$id;
        $container
            ->register($provider, '%security.authentication.provider.dao.class%')
            ->setArguments(array(new Reference($userProvider), new Reference('security.account_checker'), new Reference('security.encoder.'.$providerIds[$userProvider])));
        ;

        // listener
        $listenerId = 'security.authentication.listener.simplecas.'.$id;
        $listener = $container->setDefinition($listenerId, clone $container->getDefinition('security.authentication.listener.simplecas'));
        $arguments = $listener->getArguments();
        $arguments[1] = new Reference($provider);
        $listener->setArguments($arguments);

        $options = array(
            'check_path'                     => '/login_check',
            'login_path'                     => '/login',
            'use_forward'                    => false,
            'always_use_default_target_path' => false,
            'default_target_path'            => '/',
            'target_path_parameter'          => '_target_path',
            'use_referer'                    => false,
            'failure_path'                   => null,
            'failure_forward'                => false,
        );
        foreach (array_keys($options) as $key) {
            if (isset($config[$key])) {
                $options[$key] = $config[$key];
            }
        }
        $container->setParameter('security.authentication.form.options', $options);
        $container->setParameter('security.authentication.form.login_path', $options['login_path']);
        $container->setParameter('security.authentication.form.use_forward', $options['use_forward']);

        return array($provider, $listenerId, 'security.authentication.form_entry_point');
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'simplecas';
    }
}
