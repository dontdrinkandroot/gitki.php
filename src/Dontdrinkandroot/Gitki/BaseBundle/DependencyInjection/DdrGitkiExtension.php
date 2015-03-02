<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrGitkiExtension extends Extension implements PrependExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return "ddr_gitki";
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $twigConfig = [
            'globals' => [
                'ddr_gitki_name'                  => $config['name'],
                'ddr_gitki_show_toc'              => $config['twig']['show_toc'],
                'ddr_gitki_toc_max_level'         => $config['twig']['toc_max_level'],
                'ddr_gitki_show_breadcrumbs'      => $config['twig']['show_breadcrumbs'],
                'ddr_gitki_elasticsearch_enabled' => isset($config['elasticsearch'])
            ]
        ];

        $container->prependExtensionConfig('twig', $twigConfig);
    }

    /**
     * {@inheritdoc}
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

        if (isset($config['elasticsearch'])) {
            $container->setParameter('ddr_gitki.elasticsearch.enabled', true);
            $container->setParameter('ddr_gitki.elasticsearch.host', $config['elasticsearch']['host']);
            $container->setParameter('ddr_gitki.elasticsearch.port', $config['elasticsearch']['port']);
            $container->setParameter('ddr_gitki.elasticsearch.index_name', $config['elasticsearch']['index_name']);
        } else {
            $container->setParameter('ddr_gitki.elasticsearch.enabled', false);
        }
    }
}
