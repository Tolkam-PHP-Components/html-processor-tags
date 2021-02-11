<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags;

use SplDoublyLinkedList;
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
     * @param string $tagName
     * @param        $registration
     *
     * @return TagsHandlerInterface
     * @throws TagsMiddlewareException
     */
    private function resolveRegistration(
        string $tagName,
        $registration
    ): TagsHandlerInterface {
        
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
