<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Loader;

use Psr\Container\ContainerInterface;
use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;

class ContainerLoader implements TagsHandlerLoaderInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @inheritDoc
     */
    public function load(string $alias): ?TagsHandlerInterface
    {
        if ($this->container->has($alias)) {
            return $this->container->get($alias);
        }
        
        return null;
    }
}
