<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Loader;

use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;
use Tolkam\HTMLProcessor\Tags\TagsMiddlewareException;

trait LoadersAwareTrait
{
    /**
     * @var TagsHandlerLoaderInterface[]
     */
    private array $loaders = [];
    
    /**
     * Adds handler loader
     *
     * @param TagsHandlerLoaderInterface $loader
     *
     * @return void
     */
    public function addHandlerLoader(TagsHandlerLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }
    
    /**
     * @param string $alias
     *
     * @return TagsHandlerInterface|null
     * @throws TagsMiddlewareException
     */
    private function loadHandler(string $alias): ?TagsHandlerInterface
    {
        foreach ($this->loaders as $loader) {
            if (null !== $loaded = $loader->load($alias)) {
                
                if (!$loaded instanceof TagsHandlerInterface) {
                    throw new TagsMiddlewareException(sprintf(
                        'Loaded handler with "%s" alias must implement %s',
                        $alias,
                        TagsHandlerInterface::class
                    ));
                }
                
                return $loaded;
            }
        }
        
        throw new TagsMiddlewareException(sprintf(
            'Unable to load handler with "%s" alias. Have you added appropriate loader?',
            $alias
        ));
    }
}
