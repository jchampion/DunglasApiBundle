<?php

/*
 * This file is part of the DunglasApiBundle package.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dunglas\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Dunglas\ApiBundle\Exception\RuntimeException;

/**
 * Injects data managers in the chain.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DataProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('api.data_provider');
        if (0 === count($taggedServices)) {
            throw new RuntimeException('No DataProvider found. Did you forget to tag your own data provider?');
        }

        $sortedServices = [];
        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : 0;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }
        krsort($sortedServices);

        // Flatten the array
        $dataProviders = call_user_func_array('array_merge', $sortedServices);

        $container->getDefinition('api.data_provider')->addArgument($dataProviders);
    }
}
