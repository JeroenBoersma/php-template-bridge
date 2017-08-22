<?php

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Exception\NotFoundException;
use Srcoder\Normalize\Normalize;

interface EngineInterface
{

    /**
     * Load a file
     *
     * @param string $name
     * @return string
     */
    public function addFileAndToHtml(string $name) : string;

    /**
     * Does a file exists
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool;

    /**
     * Add a file
     *
     * @param string $name
     * @return EngineInterface
     */
    public function addFile(string $name) : EngineInterface;

    /**
     * Render template
     *
     * @param Data $data
     * @param string $name
     * @return string
     */
    public function render(Data $data = null, string $name = null) : string;

    /**
     * Lookup a file
     *
     * @param string $name
     * @return string
     * @throws NotFoundException
     */
    public function lookup(string $name) : string;

    /**
     * Append a path for lookup
     *
     * @param string $path
     * @return EngineInterface
     */
    public function appendPath(string $path) : EngineInterface;

    /**
     * Prepend a path for lookup
     *
     * @param string $path
     * @return EngineInterface
     */
    public function prependPath(string $path) : EngineInterface;

}
