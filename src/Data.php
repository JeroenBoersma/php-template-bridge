<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge;

class Data
{

    /** @var array */
    protected $data = [];

    /**
     * Data constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @param string $key
     * @return mixed|null
     */
    public function getData(string $key)
    {
        if (!$this->hasData($key)) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * Set data
     *
     * @param string $key
     * @param mixed $value
     * @return Data
     */
    public function setData(string $key, $value) : Data
    {
        $data = $this->data;
        $data[$key] = $value;

        return new self($data);
    }

    /**
     * Has data
     *
     * @param string $key
     * @return bool
     */
    public function hasData(string $key) : bool
    {
        return false !== array_search($key, array_keys($this->data));
    }

    /**
     * Get contents of data
     *
     * @return array
     */
    public function data() : array
    {
        return $this->data;
    }

}
