<?php
/**
 * @copyright (c) 2017 Srcode
 */

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\Rule\Append;
use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Exception\NotFoundException;

class Plain extends EngineAbstract
{

    /** @var array */
    protected $paths;
    /** @var string */
    protected $rootPath;

    public function __construct(array $paths = null, string $rootPath = null)
    {
        $this->normalizerInit();

        $this->paths = $paths ?? [''];
        $this->rootPath = $rootPath ?? getcwd();
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
        $path && array_push($this->paths, $path);

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
     * Return template
     *
     * @param Data $data
     * @param string $name
     * @return string
     */
    public function render(Data $data = null, string $singleFilename = null): string
    {
        $files = $this->getFiles($name);

        if (empty($files)) {
            // Nothing to render
            return '';
        }

        $data = array_filter(array_map(function($value) {
                if (is_object($value) && !method_exists($value, '__toString')) {
                    return false;
                } elseif (is_array($value)) {
                    return false;
                }

                return (string)$value;
            },
            $data ? $data->data() : []
        ));
        $keys = array_map(function($key) {
                    return "{{\${$key}}}";
                }, array_keys($data)
        );

        return implode('', array_map(function($content) use (&$keys, &$data) {
            return str_replace($keys, $data, $content);
        }, $files));
    }

    /**
     * Lookup a file
     *
     * @param string $name
     * @return string
     * @throws NotFoundException
     */
    public function lookup(string $name): string
    {
        $filename = $this->normalize($name);

        $filePath = array_reduce($this->paths, function($found, $path) use ($filename) {

            if ($found) {
                return $found;
            }

            $filePath = $path . DIRECTORY_SEPARATOR . $filename;
            if (!is_readable($filePath)) {
                return false;
            }

            return $filePath;
        }, false);

        if (false === $filePath) {
            throw new NotFoundException("Cannot find '{$filename}' in '{$this->rootPath}'.");
        }

        return file_get_contents($filePath);
    }

}
