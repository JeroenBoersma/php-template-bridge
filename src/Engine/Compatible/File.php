<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge\Engine\Compatible;

use Srcoder\TemplateBridge\Data;

class File
{
    /** @var array */
    protected $___methods = [];
    /** @var object */
    protected $___parent = null;

    public function __construct(array $___methods = [], $___parent = null)
    {
        $this->___methods = $___methods;
        $this->___parent = $___parent;
    }

    /**
     * Magic lookup
     *
     * @param string $name
     * @param array $arguments
     * @return null|mixed
     */
    public function __call(string $name, array $arguments)
    {
        $parent = $this->___parent ?? $this;

        if (isset($this->___methods[$name])) {
            /** @var \Closure $callback */
            $callback = $this->___methods[$name];
            return $callback->call($parent, ...$arguments);
        }

        if ($this->___parent) {
            return $this->___callbackParentAsThis($name, $arguments);
        }

        if (!isset($this->___methods['__call'])) {
            return null;
        }
        /** @var \Closure $callback */
        $callback = $this->___methods['__call'];
        array_unshift($arguments, $name);
        return $callback->call($parent, $arguments);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    protected function ___callbackParentAsThis(string $name, array $arguments)
    {
        $func = function () use ($name, $arguments) {
            return call_user_func_array([$this, $name], $arguments);
        };

        return $func->call($this->___parent);
    }

    /**
     * Render contents
     * @param Data $___data
     * @param string $___filePath
     * @return string
     */
    public function ___render(string $___filePath, Data $___data = null) : string
    {
        $___data = $___data ?? new Data();
        foreach ($___data->data() as $___k => $___v) {
            ${$___k} = $___v;
            unset ($___k, $___v);
        }
        unset($___data);

        ob_start();
        include $___filePath;
        return ob_get_clean();
    }

}
