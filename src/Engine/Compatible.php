<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\Rule\Append;
use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Engine\Compatible\File;
use Srcoder\TemplateBridge\Exception\InvalidParentTypeException;
use Srcoder\TemplateBridge\Exception\NotFoundException;

class Compatible extends EngineAbstract
{

    /** @var array */
    protected $paths = [];
    /** @var string */
    protected $rootPath;
    /** @var array */
    protected $methods = [];
    /** @var array */
    protected $parent = [];

    public function __construct(array $paths = [], string $rootPath = null)
    {
        $this->normalizerInit()
                ->addRule(new Append('.php'));

        $this->rootPath = $rootPath ?? getcwd();
        array_map([$this, 'appendPath'], $paths);
    }

    /**
     * Get real path
     *
     * @param string $path
     * @return string
     */
    protected function getRealPath(string $path) : string
    {
        if (0 === strpos($path, DIRECTORY_SEPARATOR)) {
            return $path;
        } else {
            $path = $this->rootPath . DIRECTORY_SEPARATOR . $path;
        }

        if (!file_exists($path . DIRECTORY_SEPARATOR . '.')) {
            return '';
        }

        return $path;
    }

    /**
     * Append path
     *
     * @param string $path
     * @return EngineInterface
     */
    public function appendPath(string $path) : EngineInterface
    {
        $path = $this->getRealPath($path);
        $path && array_push($this->paths, $this->getRealPath($path));

        return $this;
    }


    /**
     * Append path
     *
     * @param string $path
     * @return EngineInterface
     */
    public function prependPath(string $path) : EngineInterface
    {
        $path = $this->getRealPath($path);
        $path && array_unshift($this->paths, $path);

        return $this;
    }

    /**
     * Get paths
     *
     * @return array
     */
    public function getPaths() : array
    {
        return $this->paths;
    }

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
    public function render(Data $data = null, string $singleFilename = null): string
    {
        return implode('', array_map(function($filename) use ($data) {
            $compatibleClass = new File($this->methods, $this->parent);
            return $compatibleClass->___render($filename, $data);
        }, $this->getFiles($singleFilename)));
    }

    /**
     * Lookup a file
     *
     * @param string $filename
     * @return string
     * @throws NotFoundException
     */
    public function lookup(string $filename): string
    {
        $filename = $this->normalize($filename);

        $filePath = array_reduce($this->paths, function($found, $path) use ($filename) {

            if ($found) {
                return $found;
            }

            $filePath = realpath($path . DIRECTORY_SEPARATOR . $filename);
            if (false === strpos($filePath, $path)) {
                // Not allowed
                return false;
            }
            if (!is_readable($filePath)) {
                // Not found
                return false;
            }

            return $filePath;
        }, false);

        if (false === $filePath) {
            throw new NotFoundException("Cannot find '{$filename}' in '{$this->rootPath}'.");
        }

        return $filePath;
    }

}
