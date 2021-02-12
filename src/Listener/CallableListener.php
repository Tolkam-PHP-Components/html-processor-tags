<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Listener;

use Tolkam\HTMLProcessor\Context;
use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;

class CallableListener implements ResolveListenerInterface
{
    /**
     * @var callable
     */
    protected $callable;
    
    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }
    
    /**
     * @inheritDoc
     */
    public function onResolve(
        string $tagName,
        TagsHandlerInterface $handler,
        Context $context
    ): void {
        ($this->callable)($tagName, $handler, $context);
    }
}
