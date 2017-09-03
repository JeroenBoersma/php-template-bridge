<?php

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Content;
use Srcoder\TemplateBridge\Exception\NotFoundException;

interface EngineInterface
{

    /**
     * Does a file exists
     *
     * @param string $filename
     * @return bool
     */
    public function exists(string $filename) : bool;

    /**
     * Add a file
     *
     * @param string $filename
     * @return EngineInterface
     */
    public function addFile(string $filename) : EngineInterface;

    /**
     * Render template
     *
     * @param Data $data
     * @param string $singleFilename
     * @return Content
     */
    public function render(Data $data = null, string $singleFilename = null) : Content;

    /**
     * Render template for given file
     *
     * @param string $filename
     * @param Data|null $data
     * @return Content
     */
    public function addFileAndRender(string $filename, Data $data = null) : Content;

    /**
     * Lookup a file
     *
     * @param string $filename
     * @return string
     * @throws NotFoundException
     */
    public function lookup(string $filename) : string;

    /**
     * Append a lookup path
     *
     * @param string $path
     * @return EngineInterface
     */
    public function appendPath(string $path) : EngineInterface;

    /**
     * Prepend a lookup path
     *
     * @param string $path
     * @return EngineInterface
     */
    public function prependPath(string $path) : EngineInterface;

}
