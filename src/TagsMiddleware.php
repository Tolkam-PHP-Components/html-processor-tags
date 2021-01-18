<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags;

use SplObjectStorage;
use Tolkam\DOM\Manipulator\Manipulator;
use Tolkam\HTMLProcessor\MiddlewareHandlerInterface;
use Tolkam\HTMLProcessor\MiddlewareInterface;
use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;
use Tolkam\HTMLProcessor\Tags\Listener\ResolveListenerInterface;
use Tolkam\HTMLProcessor\Tags\Loader\LoadersAwareTrait;
use Tolkam\HTMLProcessor\Tags\Loader\TagsHandlerLoaderInterface;

class TagsMiddleware implements MiddlewareInterface
{
    use LoadersAwareTrait;
    
    /**
     * Handler registrations
     * @var array
     */
    private array $registrations = [];
    
    /**
     * @var SplObjectStorage|ResolveListenerInterface[]
     */
    private SplObjectStorage $resolveListeners;
    
    /**
     * @param TagsHandlerLoaderInterface[] $loaders
     */
    public function __construct(array $loaders = [])
    {
        $this->resolveListeners = new SplObjectStorage;
        
        foreach ($loaders as $loader) {
            $this->addHandlerLoader($loader);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function apply(
        Manipulator $dom,
        MiddlewareHandlerInterface $middlewareHandler
    ): Manipulator {
        
        // find each handler's target element
        foreach ($this->registrations as $tagName => $registration) {
            $found = $dom->filter($tagName);
            if ($found->count()) {
                $tags = [];
                
                $found->each(function (Manipulator $e) use (&$tags, $tagName) {
                    $tags[] = new Tag($e);
                });
                
                $handler = $this->resolveRegistration($tagName, $registration);
                $handler->handle($tagName, $tags, new NodesFactory($dom));
            }
        }
        
        return $middlewareHandler->handle($dom);
    }
    
    /**
     * Adds handler registration
     *
     * @param string                      $tagName
     * @param string|TagsHandlerInterface $registration
     *
     * @return string|TagsHandlerInterface
     * @throws TagsMiddlewareException
     */
    public function addHandler(
        string $tagName,
        $registration
    ) {
        if (isset($this->registrations[$tagName])) {
            throw new TagsMiddlewareException(sprintf(
                'Handler for "%1$s" tag is already registered. '
                . 'You may want to create another instance of middleware '
                . 'to add another "%1$s" handler',
                $tagName
            ));
        }
        
        if (!is_string($registration) && !($registration instanceof TagsHandlerInterface)) {
            throw new TagsMiddlewareException(sprintf(
                'Handler registration must be a string or implement %s',
                TagsHandlerInterface::class
            ));
        }
        
        return $this->registrations[$tagName] = $registration;
    }
    
    /**
     * Adds processor resolve listener
     *
     * @param ResolveListenerInterface $resolveListener
     *
     * @return $this
     */
    public function addResolveListener(ResolveListenerInterface $resolveListener): self
    {
        $this->resolveListeners->attach($resolveListener);
        
        return $this;
    }
    
    /**
     * Resolves registration into processor
     *
     * @param string $tagName
     * @param        $registration
     *
     * @return TagsHandlerInterface
     * @throws TagsMiddlewareException
     */
    private function resolveRegistration(string $tagName, $registration): TagsHandlerInterface
    {
        if ($registration instanceof TagsHandlerInterface) {
            $handler = $registration;
        }
        else {
            $handler = $this->loadHandler($registration);
        }
        
        foreach ($this->resolveListeners as $resolveListener) {
            $resolveListener->onResolve($tagName, $handler);
        }
        
        return $handler;
    }
}
