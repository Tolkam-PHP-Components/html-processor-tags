<?php

namespace Tolkam\HTMLProcessor\Tags\Handler;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use TypeError;

trait ParametersAwareTrait
{
    /**
     * @var array
     */
    private array $params = [];
    
    /**
     * Sets arbitrary parameters
     *
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = array_replace($this->params, $params);
    }
    
    /**
     * Sets parameter by name
     *
     * @param string $name
     * @param        $value
     */
    public function setParam(string $name, $value): void
    {
        $this->params[$name] = $value;
    }
    
    /**
     * Gets parameter by name
     *
     * @param string       $name
     * @param null         $default
     * @param Closure|null $validator
     *
     * @return mixed
     */
    public function getParam(string $name, $default = null, Closure $validator = null)
    {
        return $this->validateParam(
            $name,
            $this->params[$name] ?? $default,
            $validator
        );
    }
    
    /**
     * @param string       $name
     * @param Closure|null $validator
     *
     * @return mixed
     */
    public function getRequiredParam(string $name, Closure $validator = null)
    {
        if (!isset($this->params[$name]) && !array_key_exists($name, $this->params)) {
            throw new InvalidArgumentException(sprintf(
                'Required parameter "%s" was not set',
                $name
            ));
        }
        
        return $this->validateParam(
            $name,
            $this->params[$name],
            $validator
        );
    }
    
    /**
     * Gets all parameters
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * Clears parameters
     */
    public function clearParams(): void
    {
        $this->params = [];
    }
    
    /**
     * @param string       $name
     * @param              $value
     * @param Closure|null $validator
     *
     * @return mixed
     */
    private function validateParam(string $name, $value, Closure $validator = null)
    {
        if (!$validator) {
            return $value;
        }
        
        $message = sprintf('Parameter "%s" did not pass supplied validator', $name);
        
        try {
            $result = $validator($value);
            
            if ($result === false) {
                throw new TypeError($message);
            }
            
            return $value;
        } catch (Throwable $t) {
            $thrownMessage = $t->getMessage();
            if ($thrownMessage !== $message) {
                $message .= ' (' . $thrownMessage . ')';
            }
            
            throw new RuntimeException(sprintf(
                $message,
                $name
            ));
        }
    }
}
