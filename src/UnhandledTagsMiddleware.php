<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags;

use Tolkam\DOM\Manipulator\Manipulator;
use Tolkam\HTMLProcessor\MiddlewareHandlerInterface;
use Tolkam\HTMLProcessor\MiddlewareInterface;

class UnhandledTagsMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    protected string $tagsPrefix;
    
    /**
     * @param string $tagsPrefix
     */
    public function __construct(string $tagsPrefix = 'x-')
    {
        $this->tagsPrefix = $tagsPrefix;
    }
    
    /**
     * @inheritDoc
     */
    public function apply(
        Manipulator $dom,
        MiddlewareHandlerInterface $middlewareHandler
    ): Manipulator {
        $xPath = "//*[starts-with(local-name(), '" . $this->tagsPrefix . "')]";
        $found = $dom->filterXPath($xPath);
        
        if ($found->count()) {
            $found->each(function (Manipulator $e) use ($dom) {
                $comment = $dom->createComment('unhandled: ' . $e->nodeName());
                $e->replaceWith($comment);
            });
        }
        
        return $middlewareHandler->handle($dom);
    }
}
