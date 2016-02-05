<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        // When you install a third-party bundle or create a new bundle in your
        // application, you must add it in the following array to register it
        // in the application. Otherwise, the bundle won't be enabled and you
        // won't be able to use it.
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new CodeExplorerBundle\CodeExplorerBundle(),
            new AppBundle\AppBundle(),
            new RestBundle\RestBundle(),
            new OAuthBundle\OAuthBundle(),
            new RedisBundle\RedisBundle(),
            new RabbitMQBundle\RabbitMQBundle(),
            new ElasticSearchBundle\ElasticSearchBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new Bazinga\Bundle\RestExtraBundle\BazingaRestExtraBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new FOS\ElasticaBundle\FOSElasticaBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        );

        // Some bundles are only used while developing the application or during
        // the unit and functional tests. Therefore, they are only registered
        // when the application runs in 'dev' or 'test' environments. This allows
        // to increase application performance in the production environment.
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
