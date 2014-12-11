<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrGitkiExtension extends Extension implements PrependExtensionInterface
{

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return "ddr_gitki";
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configs = $container->getExtensionConfig($this->getAlias());
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $hwiOauthConfig = array('resource_owners' => array());
        $securityConfig = array('firewalls' => array('secured_area' => array('oauth' => array('resource_owners' => array()))));
        $twigConfig = array('globals' => array());

        $twigConfig['globals']['ddr_gitki_name'] = $config['name'];
        $twigConfig['globals']['ddr_gitki_show_toc'] = $config['twig']['show_toc'];
        $twigConfig['globals']['ddr_gitki_toc_max_level'] = $config['twig']['toc_max_level'];
        $twigConfig['globals']['ddr_gitki_show_breadcrumbs'] = $config['twig']['show_breadcrumbs'];

        if (isset($config['authentication']['oauth']['default_provider'])) {
            $securityConfig['firewalls']['secured_area']['oauth']['login_path'] = '/connect/' . $config['authentication']['oauth']['default_provider'];
        } else {
            $securityConfig['firewalls']['secured_area']['oauth']['login_path'] = '/login';
        }

        $formLoginEnabled = true;
        if (isset($config['authentication']['form_login_enabled'])) {
            $formLoginEnabled = $config['authentication']['form_login_enabled'];
        }
        $twigConfig['globals']['ddr_gitki_form_login_enabled'] = $formLoginEnabled;

        if (isset($config['authentication']['oauth']['providers']['github'])) {

            $googleConfig = $config['authentication']['oauth']['providers']['github'];
            $hwiOauthConfig['fosub']['properties']['github'] = 'githubId';
            $hwiOauthConfig['resource_owners']['github'] = array(
                'type' => 'github',
                'client_id' => $googleConfig['client_id'],
                'client_secret' => $googleConfig['secret'],
                'scope' => 'user:email'
            );
            $securityConfig['firewalls']['secured_area']['oauth']['resource_owners']['github'] = '/login/check-github';
        }

        if (isset($config['authentication']['oauth']['providers']['google'])) {

            $googleConfig = $config['authentication']['oauth']['providers']['google'];
            $hwiOauthConfig['fosub']['properties']['google'] = 'googleId';
            $hwiOauthConfig['resource_owners']['google'] = array(
                'type' => 'google',
                'client_id' => $googleConfig['client_id'],
                'client_secret' => $googleConfig['secret'],
                'scope' => 'email profile'
            );
            $securityConfig['firewalls']['secured_area']['oauth']['resource_owners']['google'] = '/login/check-google';
        }

        $container->prependExtensionConfig('security', $securityConfig);
        $container->prependExtensionConfig('hwi_oauth', $hwiOauthConfig);
        $container->prependExtensionConfig('twig', $twigConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->doLoad($config, $container);
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }

    protected function doLoad($config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('ddr_gitki_base.repository_path', $config['repository_path']);
        $container->setParameter('ddr_gitki_base.name', $config['name']);
    }
}
