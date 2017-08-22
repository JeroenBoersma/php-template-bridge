<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge;

use Srcoder\TemplateBridge\Engine\EngineInterface;
use Srcoder\TemplateBridge\Exception\ExistsException;
use Srcoder\TemplateBridge\Exception\NotFoundException;

class Manager
{

    /** @var array */
    protected $engines = [];
    /** @var bool */
    protected $sorted = true;

    /** @var Manager */
    static protected $self;

    /**
     * Get instance
     *
     * @return Manager
     */
    static public function instance() : Manager
    {
        if (null === self::$self) {
            self::$self = new self();
        }

        return self::$self;
    }

    /**
     * Engine exists
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool
    {
        return isset($this->engines[$name]);
    }

    /**
     * Add a new engine
     *
     * @param string $name              Name for engine
     * @param EngineInterface $engine   Add a valid engine
     * @param int $priority             Highest is most important
     * @return Manager
     * @throws ExistsException
     */
    public function add(string $name, EngineInterface $engine, int $priority) : Manager
    {
        if ($this->exists($name)) {
            throw new ExistsException("Engine with name '{$name}' already exist.");
        }
        $this->engines[$name] = [
                'priority' => abs(+$priority),
                'engine' => $engine
        ];
        $this->sorted = false;

        return $this;
    }

    /**
     * Removed engine
     *
     * @param string $name
     * @return Manager
     * @throws NotFoundException
     */
    public function remove(string $name) : Manager
    {
        if (!$this->exists($name)) {
            throw new NotFoundException("Engine with name '{$name}' does not exist.");
        }
        unset ($this->engines[$name]);

        return $this;
    }

    /**
     * Get specific engine
     *
     * @param string $name
     * @return EngineInterface
     * @throws NotFoundException
     */
    public function get(string $name) : EngineInterface
    {
        if (!$this->exists($name)) {
            throw new NotFoundException("Engine with name '{$name}' does not exist.");
        }

        return $this->engines[$name]['engine'];
    }

    /**
     * Get engines
     *
     * @return array
     */
    public function getEngines()
    {
        if (!$this->sorted) {
            uasort($this->engines, function($engineA, $engineB){
                return $engineA['priority'] <=> $engineB['priority'];
            });
            $this->engines = array_reverse($this->engines, true);
            $this->sorted = true;
        }

        return array_map(function($engine) {
            return $engine['engine'];
        }, $this->engines);
    }

    /**
     * Lookup file in one of the engines, highest priority is first
     * If found, we'll stop looking
     *
     * @param string $name
     * @param bool $silence
     * @return Manager
     *
     * @throws NotFoundException
     */
    public function addFile(string $name, bool $silence = true) : Manager
    {
        $fileFound = array_reduce($this->getEngines(), function($fileFound, EngineInterface $engine) use ($name) {
            if ($fileFound) {
                return true;
            }

            if ($engine->exists($name)) {
                return true;
            }

            try {
                $engine->addFile($name);
                return true;
            } catch (NotFoundException $e) {}

            return false;
        }, false);

        if (!$silence && !$fileFound) {
            throw new NotFoundException("File '{$name}' not found in any engine.");
        }

        return $this;
    }

    /**
     * Render all data, highest priority is last
     *
     * @param Data $data
     * @param string $name
     * @return string
     */
    public function render(Data $data = null, string $name = null) : string
    {
        return array_reduce(
                array_reverse($this->getEngines()),
                function(string $html, EngineInterface $engine) use ($data, $name) {
                    return $html . $engine->render($data, $name);
                },
                ''
        );
    }

}
