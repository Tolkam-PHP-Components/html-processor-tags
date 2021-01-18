<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags;

use DOMComment;
use DOMElement;
use Tolkam\DOM\Manipulator\Manipulator;

class NodesFactory
{
    /**
     * @var Manipulator
     */
    protected Manipulator $dom;
    
    /**
     * @param Manipulator $dom
     */
    public function __construct(Manipulator $dom)
    {
        $this->dom = $dom;
    }
    
    /**
     * @param string     $name
     * @param mixed|null $children
     * @param array      $attributes
     *
     * @return DOMElement
     */
    public function element(string $name, $children = '', array $attributes = []): DOMElement
    {
        return $this->dom->createElement($name, $children, $attributes);
    }
    
    /**
     * @param string $contents
     *
     * @return DOMComment
     */
    public function comment(string $contents): DOMComment
    {
        return $this->dom->createComment($contents);
    }
}
