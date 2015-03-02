<?php

namespace Dontdrinkandroot\Gitki\WebBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrGitkiWebExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $hwiOauthConfig = [
            'resource_owners' => []
        ];

//        $securityConfig = [
//            'firewalls' => [
//                'secured_area' => [
//                    'oauth' => [
//                        'resource_owners' => []
//                    ]
//                ]
//            ]
//        ];

//        if (isset($config['authentication']['oauth']['default_provider'])) {
//            $securityConfig['firewalls']['secured_area']['oauth']['login_path'] =
//                '/connect/' . $config['authentication']['oauth']['default_provider'];
//        } else {
//            $securityConfig['firewalls']['secured_area']['oauth']['login_path'] = '/login';
//        }

        $formLoginEnabled = true;
        if (isset($config['authentication']['form_login_enabled'])) {
            $formLoginEnabled = $config['authentication']['form_login_enabled'];
        }
        $twigConfig['globals']['ddr_gitki_form_login_enabled'] = $formLoginEnabled;

        if (isset($config['authentication']['oauth']['providers']['github'])) {
            $githubConfig = $config['authentication']['oauth']['providers']['github'];
            $hwiOauthConfig['fosub']['properties']['github'] = 'githubId';
            $hwiOauthConfig['resource_owners']['github'] = [
                'type'          => 'github',
                'client_id'     => $githubConfig['client_id'],
                'client_secret' => $githubConfig['secret'],
                'scope'         => 'user:email'
            ];
//            $securityConfig['firewalls']['secured_area']['oauth']['resource_owners']['github'] = '/login/check-github';
        }

        if (isset($config['authentication']['oauth']['providers']['google'])) {
            $googleConfig = $config['authentication']['oauth']['providers']['google'];
            $hwiOauthConfig['fosub']['properties']['google'] = 'googleId';
            $hwiOauthConfig['resource_owners']['google'] = [
                'type'          => 'google',
                'client_id'     => $googleConfig['client_id'],
                'client_secret' => $googleConfig['secret'],
                'scope'         => 'email profile',
                'options'       => [
                    'access_type'     => 'online',
                    'approval_prompt' => 'auto',
                    'display'         => 'page',
                    'login_hint'      => 'email address'
                ]
            ];
//            $securityConfig['firewalls']['secured_area']['oauth']['resource_owners']['google'] = '/login/check-google';
        }

//        $container->prependExtensionConfig('security', $securityConfig);
        $container->prependExtensionConfig('hwi_oauth', $hwiOauthConfig);
        $container->prependExtensionConfig('twig', $twigConfig);
    }
}
