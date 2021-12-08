<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests;

use Nanofelis\Bundle\JsonRpcBundle\NanofelisJsonRpcBundle;
use Nanofelis\Bundle\JsonRpcBundle\Tests\Service\MockService;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutingPhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

class TestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new NanofelisJsonRpcBundle(),
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import(__DIR__.'/../Resources/config/routing/routing.xml');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'test' => true,
            'serializer' => [
                'enabled' => true,
            ],
        ]);
        $c->loadFromExtension('sensio_framework_extra', [
            'router' => [
                'annotations' => false,
            ],
        ]);
        $c->setParameter('kernel.secret', 'fake');
    }

    public function process(ContainerBuilder $c)
    {
        $c->register(MockService::class, MockService::class)
            ->addTag('nanofelis_json_rpc')
            ->setPublic(true);
    }

    /**
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $file = (new \ReflectionObject($this))->getFileName();
        /* @var RoutingPhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file, 'php');
        $kernelLoader->setCurrentDir(\dirname($file));
        $collection = new RouteCollection();

        $configureRoutes = new \ReflectionMethod($this, 'configureRoutes');
        $configuratorClass = $configureRoutes->getNumberOfParameters() > 0 && ($type = $configureRoutes->getParameters()[0]->getType()) instanceof \ReflectionNamedType && !$type->isBuiltin() ? $type->getName() : null;

        if ($configuratorClass && !is_a(RoutingConfigurator::class, $configuratorClass, true)) {
            trigger_deprecation('symfony/framework-bundle', '5.1', 'Using type "%s" for argument 1 of method "%s:configureRoutes()" is deprecated, use "%s" instead.', RouteCollectionBuilder::class, self::class, RoutingConfigurator::class);

            $routes = new RouteCollectionBuilder($loader);
            $this->configureRoutes($routes);

            return $routes->build();
        }

        $configureRoutes->getClosure($this)(new RoutingConfigurator($collection, $kernelLoader, $file, $file, $this->getEnvironment()));

        foreach ($collection as $route) {
            $controller = $route->getDefault('_controller');

            if (\is_array($controller) && [0, 1] === array_keys($controller) && $this === $controller[0]) {
                $route->setDefault('_controller', ['kernel', $controller[1]]);
            }
        }

        return $collection;
    }
}
