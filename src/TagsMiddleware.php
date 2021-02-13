<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags;

use SplDoublyLinkedList;
use Tolkam\DOM\Manipulator\Manipulator;
use Tolkam\HTMLProcessor\Context;
use Tolkam\HTMLProcessor\MiddlewareHandlerInterface;
use Tolkam\HTMLProcessor\MiddlewareInterface;
use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;
use Tolkam\HTMLProcessor\Tags\Listener\ResolveListenerInterface;
use Tolkam\HTMLProcessor\Tags\Loader\TagsHandlerLoaderInterface;

class TagsMiddleware implements MiddlewareInterface
{
    /**
     *  Position of the resolve listener in the queue
     */
    public const LISTENER_PREPEND = 1;
    public const LISTENER_APPEND  = 2;
    
    /**
     * Handler registrations
     * @var array
     */
    private array $registrations = [];
    
    /**
     * @var TagsHandlerLoaderInterface[]
     */
    private array $loaders = [];
    
    /**
     * @var SplDoublyLinkedList|ResolveListenerInterface[]
     */
    private SplDoublyLinkedList $resolveListeners;
    
    /**
     * @param TagsHandlerLoaderInterface[] $loaders
     */
    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->addHandlerLoader($loader);
        }
        
        $this->resolveListeners = new SplDoublyLinkedList;
    }
    
    /**
     * @inheritDoc
     */
    public function apply(
        Manipulator $dom,
        MiddlewareHandlerInterface $middlewareHandler,
        Context $context
    ): Manipulator {
        
        // find each handler's target element
        foreach ($this->registrations as $tagName => $registration) {
            $found = $dom->filter($tagName);
            if ($found->count()) {
                $tags = [];
                
                $found->each(function (Manipulator $e) use (&$tags, $tagName) {
                    $tags[] = new Tag($e);
                });
                
                $handler = $this->resolveRegistration($tagName, $registration, $context);
                $handler->handle($tagName, $tags, $context, new NodesFactory($dom));
            }
        }
        
        return $middlewareHandler->handle($dom, $context);
    }
    
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
     * @param int                      $position
     *
     * @return $this
     */
    public function addResolveListener(
        ResolveListenerInterface $resolveListener,
        int $position = self:: LISTENER_APPEND
    ): self {
        
        if ($position === self::LISTENER_APPEND) {
            $this->resolveListeners->push($resolveListener);
        }
        
        if ($position === self::LISTENER_PREPEND) {
            $this->resolveListeners->unshift($resolveListener);
        }
        
        return $this;
    }
    
    /**
     * Resolves registration into processor
     *
     * @param string  $tagName
     * @param         $registration
     * @param Context $context
     *
     * @return TagsHandlerInterface
     * @throws TagsMiddlewareException
     */
    private function resolveRegistration(
        string $tagName,
        $registration,
        Context $context
    ): TagsHandlerInterface {
        
        if ($registration instanceof TagsHandlerInterface) {
            $handler = $registration;
        }
        else {
            $handler = $this->loadHandler($registration);
        }
        
        foreach ($this->resolveListeners as $resolveListener) {
            $resolveListener->onResolve($tagName, $handler, $context);
        }
        
        return $handler;
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
