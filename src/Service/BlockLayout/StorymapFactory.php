<?php
namespace Storymap\Service\BlockLayout;

use Interop\Container\ContainerInterface;
use Storymap\Site\BlockLayout\Storymap;
use Zend\ServiceManager\Factory\FactoryInterface;

class StorymapFactory implements FactoryInterface
{
    /**
     * Create the Storymap block layout service.
     *
     * @param ContainerInterface $services
     * @return Storymap
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $apiManager = $services->get('Omeka\ApiManager');
        $formElementManager = $services->get('FormElementManager');
        $config = $services->get('Config');
        $useExternal = $config['assets']['use_externals'];
        return new Storymap($apiManager, $formElementManager, $useExternal);
    }
}
