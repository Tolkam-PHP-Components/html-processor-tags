<?php declare(strict_types=1);

namespace Tolkam\HTMLProcessor\Tags\Handler;

interface ParametersAwareInterface
{
    /**
     * Sets arbitrary parameters
     *
     * @param array $params
     */
    public function setParams(array $params): void;
    
    /**
     * Sets parameter by name
     *
     * @param string $name
     * @param        $value
     */
    public function setParam(string $name, $value): void;
    
    /**
     * Gets parameter by name
     *
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getParam(string $name, $default = null);
    
    /**
     * Gets all parameters
     *
     * @return array
     */
    public function getParams(): array;
    
    /**
     * Clears parameters
     */
    public function clearParams(): void;
}
