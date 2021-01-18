<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Listener;

use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;

interface ResolveListenerInterface
{
    /**
     * Intercepts handler resolution
     *
     * @param string               $tagName
     * @param TagsHandlerInterface $handler
     *
     * @return void
     */
    public function onResolve(string $tagName, TagsHandlerInterface $handler): void;
}
