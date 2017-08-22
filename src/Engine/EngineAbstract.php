<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\NormalizeTrait;
use Srcoder\TemplateBridge\Exception\ExistsException;

abstract class EngineAbstract implements EngineInterface
{

    /** @var array */
    protected $files = [];

    /** Import normalize trait */
    use NormalizeTrait;

    /**
     * Check if name exists in filelist
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool
    {
        return isset($this->files[$name]);
    }

    /**
     * Add a file to the stack
     *
     * @param string $name
     * @return EngineInterface
     * @throws ExistsException
     */
    public function addFile(string $name) : EngineInterface
    {
        if ($this->exists($name)) {
            throw new ExistsException("File '${name}' is already defined.");
        }
        $this->files[$name] = $this->lookup($name);

        return $this;
    }

    /**
     * Load file
     *
     * @param string $name
     * @return string
     */
    public function addFileAndToHtml(string $name) : string
    {
        return $this->addFile($name)
                ->render();
    }

    /**
     * Get string for Engine
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->render();
    }

    /**
     * Get file
     *
     * @param string $name
     * @return string
     */
    public function getFile(string $name) : string
    {
        return $this->files[$name] ?? '';
    }

    /**
     * Get files
     *
     * @param string|null $name
     * @return array
     */
    public function getFiles(string $name = null) : array
    {
        if (null === $name) {
            return $this->files;
        }

        $file = $this->getFile($name);

        if (!$file) {
            return [];
        }

        return [$name => $file];
    }

}
