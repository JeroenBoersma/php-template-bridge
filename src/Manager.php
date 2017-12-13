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
    public function addEngine(string $name, EngineInterface $engine, int $priority) : Manager
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
     * @param string $filename
     * @param bool $silence
     * @return Manager
     *
     * @throws NotFoundException
     */
    public function addFile(string $filename, bool $silence = true) : Manager
    {
        $engines = $this->getEngines();

        $fileFound = array_filter($engines, function(EngineInterface $engine) use ($filename) {
            return $engine->exists($filename);
        });

        if (!empty($fileFound)) {
            return $this;
        }

        $fileFound = array_reduce($engines, function($fileFound, EngineInterface $engine) use ($filename) {
            if ($fileFound) {
                return true;
            }

            try {
                $engine->addFile($filename);
                return true;
            } catch (NotFoundException $e) {}

            return false;
        }, false);

        if (!$silence && !$fileFound) {
            throw new NotFoundException("File '{$filename}' not found in any engine.");
        }

        return $this;
    }

    /**
     * Render all data, highest priority is last
     *
     * @param Data $data
     * @param string $filename
     * @return Content
     */
    public function render(Data $data = null, string $filename = null) : Content
    {
        $content = new Content('', true);

        return array_reduce(
                array_reverse($this->getEngines()),
                function(Content $content, EngineInterface $engine) use ($data, $filename) {
                    $rendered = $engine->render($data, $filename);

                    if ($rendered->isReturn() && $content->isReturn()) {
                        return $rendered;
                    }
                    return $content->append($rendered->toString());
                },
                $content
        );
    }

}
