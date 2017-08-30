<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\NormalizeTrait;
use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Content;
use Srcoder\TemplateBridge\Exception\ExistsException;
use Srcoder\TemplateBridge\Exception\NotFoundException;

abstract class EngineAbstract implements EngineInterface
{
    /** @var array */
    protected $paths = [];
    /** @var string */
    protected $rootPath = '';
    /** @var array */
    protected $filepaths = [];

    /** Import normalize trait */
    use NormalizeTrait;

    public function __construct($paths = [], $rootPath = null)
    {
        $this->normalizerInit();

        $this->rootPath = $rootPath ?? getcwd();
        array_map([$this, 'appendPath'], $paths);
    }

    /**
     * Get real path
     *
     * @param string $path
     * @return string
     */
    protected function getRealPath(string $path): string
    {
        if (0 === strpos($path, DIRECTORY_SEPARATOR)) {
            return $path;
        } else {
            $path = $this->rootPath . DIRECTORY_SEPARATOR . $path;
        }

        return $path;
    }

    /**
     * Append path
     *
     * @param string $path
     * @return EngineInterface
     */
    public function appendPath(string $path): EngineInterface
    {
        $path && array_push($this->paths, $path);

        return $this;
    }


    /**
     * Append path
     *
     * @param string $path
     * @return EngineInterface
     */
    public function prependPath(string $path): EngineInterface
    {
        $path && array_unshift($this->paths, $path);

        return $this;
    }

    /**
     * Check if name exists in filelist
     *
     * @param string $filename
     * @return bool
     */
    public function exists(string $filename) : bool
    {
        return isset($this->filepaths[$filename]);
    }

    /**
     * Add a file to the stack
     *
     * @param string $filename
     * @return EngineInterface
     * @throws ExistsException
     */
    public function addFile(string $filename) : EngineInterface
    {
        if ($this->exists($filename)) {
            throw new ExistsException("File '${filename}' is already defined.");
        }
        $this->addFilepath($filename, $this->lookup($filename));

        return $this;
    }

    /**
     *
     * @param string $filename
     * @param Data|null $data
     * @return Content
     */
    public function addFileAndRender(string $filename, Data $data = null) : Content
    {
        return $this->addFile($filename)
                ->render($data, $filename);
    }

    /**
     * Get string for Engine
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->render()
                ->__toString();
    }

    /**
     * Add filepath
     *
     * @param string $filename
     * @return string
     */
    protected function addFilepath(string $filename, string $filepath) : EngineInterface
    {
        $this->filepaths[$filename] = $filepath;
        return $this;
    }

    /**
     * Get file
     *
     * @param string $filename
     * @return string
     */
    public function getFilepath(string $filename) : string
    {
        return $this->filepaths[$filename] ?? '';
    }

    /**
     * Get filepaths
     *
     * @param string|null $name
     * @return array
     */
    public function getFilePaths(string $singleFilename = null) : array
    {
        if (null === $singleFilename) {
            return $this->filepaths;
        }

        $filepath = $this->getFilepath($singleFilename);

        if (!$filepath) {
            return [];
        }

        return [$singleFilename => $filepath];
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

            $filePath = $path . DIRECTORY_SEPARATOR . $filename;
            if (!is_readable($this->getRealPath($filePath))) {
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
