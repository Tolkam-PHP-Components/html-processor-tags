<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Handler;

abstract class AbstractHandler implements TagsHandlerInterface, ParametersAwareInterface
{
    use ParametersAwareTrait;
}
