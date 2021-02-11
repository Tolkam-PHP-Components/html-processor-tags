<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Loader;

use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;

interface TagsHandlerLoaderInterface
{
    /**
     * Loads the handler
     *
     * @param string $alias
     *
     * @return TagsHandlerInterface|null
     */
    public function load(string $alias): ?TagsHandlerInterface;
}
