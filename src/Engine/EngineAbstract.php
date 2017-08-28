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

abstract class EngineAbstract implements EngineInterface
{

    /** @var array */
    protected $filepaths = [];

    /** Import normalize trait */
    use NormalizeTrait;

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
        $this->filepaths[$filename] = $this->lookup($filename);

        return $this;
    }

    /**
     *
     * @param string $filename
     * @param Data|null $data
     * @return Content
     */
    public function addFileAndRender(string $filename, Data $data = null): Content
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

}
