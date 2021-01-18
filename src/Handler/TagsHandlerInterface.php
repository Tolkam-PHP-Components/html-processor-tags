<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Handler;

use Tolkam\HTMLProcessor\Tags\NodesFactory;
use Tolkam\HTMLProcessor\Tags\Tag;

interface TagsHandlerInterface
{
    /**
     * Processes matched tags
     *
     * @param string       $tagName
     * @param Tag[]        $tags
     * @param NodesFactory $factory
     *
     * @return void
     */
    public function handle(string $tagName, array $tags, NodesFactory $factory): void;
}
