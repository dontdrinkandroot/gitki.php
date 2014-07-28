<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrGitkiExtension extends Extension implements PrependExtensionInterface
{

    public function getAlias()
    {
        return "ddr_gitki";
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('ddr_gitki_base.repository_path', $config['repository_path']);
        $container->setParameter('ddr_gitki_base.name', $config['name']);

        if (isset($config['oauth']) && isset($config['oauth']['github'])) {
            $container->setParameter('ddr_gitki_base.github_users_admin', $config['oauth']['github']['users_admin']);
            $container->setParameter('ddr_gitki_base.github_users_commit', $config['oauth']['github']['users_commit']);
            $container->setParameter('ddr_gitki_base.github_users_watch', $config['oauth']['github']['users_watch']);
        } else {
            $container->setParameter('ddr_gitki_base.github_users_admin', array());
            $container->setParameter('ddr_gitki_base.github_users_commit', array());
            $container->setParameter('ddr_gitki_base.github_users_watch', array());
        }

        if (isset($config['oauth']) && isset($config['oauth']['google'])) {
            $container->setParameter('ddr_gitki_base.google_users_admin', $config['oauth']['google']['users_admin']);
            $container->setParameter('ddr_gitki_base.google_users_commit', $config['oauth']['google']['users_commit']);
            $container->setParameter('ddr_gitki_base.google_users_watch', $config['oauth']['google']['users_watch']);
        } else {
            $container->setParameter('ddr_gitki_base.google_users_admin', array());
            $container->setParameter('ddr_gitki_base.google_users_commit', array());
            $container->setParameter('ddr_gitki_base.google_users_watch', array());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $hwiOauthConfig = array('resource_owners' => array());
        $securityConfig = array('firewalls' => array('secured_area' => array('oauth' => array('resource_owners' => array()))));
        $twigConfig = array('globals' => array());

        $twigConfig['globals']['ddr_gitki_name'] = $config['name'];
        $twigConfig['globals']['ddr_gitki_show_toc'] = $config['twig']['show_toc'];
        $twigConfig['globals']['ddr_gitki_show_breadcrumbs'] = $config['twig']['show_breadcrumbs'];

        if (isset($config['oauth']['github'])) {

            $gitHubConfig = $config['oauth']['github'];
            $hwiOauthConfig['resource_owners']['github'] = array(
                'type' => 'github',
                'client_id' => $gitHubConfig['client_id'],
                'client_secret' => $gitHubConfig['secret'],
                'scope' => 'user:email,public_repo'
            );
            $securityConfig['firewalls']['secured_area']['oauth']['resource_owners']['github'] = '/login/check-github';
        }

        if (isset($config['oauth']['google'])) {

            $gitHubConfig = $config['oauth']['google'];
            $hwiOauthConfig['resource_owners']['google'] = array(
                'type' => 'google',
                'client_id' => $gitHubConfig['client_id'],
                'client_secret' => $gitHubConfig['secret'],
                'scope' => 'email profile'
            );
            $securityConfig['firewalls']['secured_area']['oauth']['resource_owners']['google'] = '/login/check-google';
        }


        $container->prependExtensionConfig('security', $securityConfig);
        $container->prependExtensionConfig('hwi_oauth', $hwiOauthConfig);
        $container->prependExtensionConfig('twig', $twigConfig);
    }
}
