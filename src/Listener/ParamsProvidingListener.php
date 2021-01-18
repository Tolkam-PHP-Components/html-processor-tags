<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Listener;

use Tolkam\HTMLProcessor\Tags\Handler\ParametersAwareInterface;
use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;

class ParamsProvidingListener implements ResolveListenerInterface
{
    /**
     * @var array
     */
    private array $params = [];
    
    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }
    
    /**
     * @inheritDoc
     */
    public function onResolve(
        string $tagName,
        TagsHandlerInterface $handler
    ): void {
        if ($handler instanceof ParametersAwareInterface) {
            $handler->setParams($this->params);
        }
    }
}
