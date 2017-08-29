<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\Rule\Append;
use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Content;
use Srcoder\TemplateBridge\Engine\Compatible\File;
use Srcoder\TemplateBridge\Exception\InvalidParentTypeException;

class Compatible extends EngineAbstract
{

    /** @var array */
    protected $methods = [];
    /** @var array */
    protected $parent = [];

    /**
     * Get defined closure
     *
     * @param string $method
     * @return \Closure
     */
    public function getMethod(string $method) : \Closure
    {
        if (!isset($this->methods[$method])) {
            return function(){};
        }

        return $this->methods[$method];
    }

    /**
     * Call defined closure
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function callMethod(string $method, array $arguments= [])
    {
        return call_user_func_array($this->getMethod($method), $arguments);
    }

    /**
     * Add compatible methods
     *
     * @param string $name
     * @param \Closure $callback
     * @return Compatible
     */
    public function addMethod(string $name, \Closure $callback) : Compatible
    {
        $this->methods[$name] = $callback;
        return $this;
    }

    /**
     * Add compatible methods
     *
     * @param array $methods
     * @return Compatible
     */
    public function addMethods(array $methods) : Compatible
    {
        array_map([$this, 'addMethod'], array_keys($methods), $methods);
        return $this;
    }

    /**
     * Add magic lookup
     *
     * @param \Closure $callback
     * @return Compatible
     */
    public function addMagic(\Closure $callback) : Compatible
    {
        $this->methods['__call'] = $callback;
        return $this;
    }

    /**
     * Add a parent class
     *
     * @param \object $class
     * @return Compatible
     *
     * @throws \Exception
     */
    public function setParent($class) : Compatible
    {
        if (!is_object($class)) {
            throw new InvalidParentTypeException('Invalid parent type');
        }
        $this->parent = $class;
        return $this;
    }

    /**
     * Return template
     *
     * @param Data $data
     * @param string $singleFilename
     * @return string
     */
    public function render(Data $data = null, string $singleFilename = null): Content
    {
        $content = new Content('', true);
        $filePaths = $this->getFilePaths($singleFilename);

        return array_reduce($filePaths, function(Content $content, $filename) use ($data, $content) {
            $compatibleClass = new File($this->methods, $this->parent);
            $rendered = $compatibleClass->___render($this->getRealPath($filename), $data);

            if ($rendered->isReturn() && $content->isReturn()) {
                return $rendered;
            }
            $content->append($rendered->__toString());

            return $content;
        }, $content);
    }

}
