<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags;

use DOMElement;
use DOMNode;
use InvalidArgumentException;
use Tolkam\DOM\Manipulator\Manipulator;
use Tolkam\Utils\Str;

/**
 * Encapsulates common element operations
 */
class Tag
{
    /**
     * @var Manipulator
     */
    private Manipulator $element;
    
    /**
     * @var array
     */
    private array $data = [];
    
    /**
     * @param Manipulator $element
     */
    public function __construct(Manipulator $element)
    {
        $node = $element->getNode(0);
        
        if (!$node instanceof DOMElement) {
            throw new InvalidArgumentException(sprintf(
                'Tag element must be an instance of "%s", "%s" given',
                DOMElement::class,
                get_class($node)
            ));
        }
        
        $count = $element->count();
        if ($count > 1) {
            throw new InvalidArgumentException(sprintf(
                'Single tag element expected, %s elements given',
                $count
            ));
        }
        
        $this->element = $element;
    }
    
    /**
     * Gets underlying element
     *
     * @return Manipulator
     */
    public function getElement(): Manipulator
    {
        return $this->element;
    }
    
    /**
     * Gets parameter value from element attribute
     *
     * @param string $name
     * @param null   $default
     *
     * @return string|null
     */
    public function getParam(string $name, $default = null): ?string
    {
        return $this->element->getAttribute($name) ?? $default;
    }
    
    /**
     * Gets params
     *
     * @param bool $cast
     *
     * @return array
     */
    public function getParams(bool $cast = true): array
    {
        $params = [];
        
        /** @var DOMNode $attribute */
        foreach ($this->element->getNode(0)->attributes as $attribute) {
            $name = Str::camelCase($attribute->nodeName);
            $value = $attribute->nodeValue;
            
            if ($cast) {
                $value = $value === 'true' ? true : $value;
                $value = $value === 'false' ? false : $value;
            }
            
            $params[$name] = $value;
        }
        
        return $params;
    }
    
    /**
     * Gets inner html content
     *
     * @param bool $asHtml
     *
     * @return string
     */
    public function getBody(bool $asHtml = false): string
    {
        if (!$asHtml) {
            return $this->element->text();
        }
        
        return $this->element->html();
    }
    
    /**
     * Gets children
     *
     * @return Manipulator
     */
    public function getChildren(): Manipulator
    {
        return $this->element->children();
    }
    
    /**
     * Replaces element with new contents
     *
     * @param string|DOMNode|iterable $contents
     */
    public function replaceWith($contents): void
    {
        $this->element->replaceWith($contents);
    }
    
    /**
     * Filters the list of nodes with a CSS selector
     *
     * @param string $selector
     *
     * @return Manipulator
     */
    public function filter(string $selector): Manipulator
    {
        return $this->element->evaluate($selector);
    }
    
    /**
     * Sets arbitrary data
     *
     * @param string $name
     * @param        $value
     *
     * @return $this
     */
    public function setData(string $name, $value): self
    {
        $this->data[$name] = $value;
        
        return $this;
    }
    
    /**
     * Checks if data exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasData(string $name): bool
    {
        return isset($this->data[$name]) && array_key_exists($name, $this->data);
    }
    
    /**
     * Gets stored data by key name
     *
     * @param string|null $name
     * @param null        $default
     *
     * @return mixed
     */
    public function getData(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->data;
        }
        
        return $this->data[$name] ?? $default;
    }
}
