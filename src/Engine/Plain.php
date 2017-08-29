<?php
/**
 * @copyright (c) 2017 Srcode
 */

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\Rule\Append;
use Srcoder\TemplateBridge\Data;

class Plain extends EngineAbstract
{

    /** @var array */
    protected $contents = [];

    /**
     * Return template
     *
     * @param Data $data
     * @param string $name
     * @return string
     */
    public function render(Data $data = null, string $singleFilename = null): string
    {
        $filepaths = $this->getFilePaths($singleFilename);

        if (empty($filepaths)) {
            // Nothing to render
            return '';
        }

        $data = array_filter(array_map(function ($value) {
            if (is_object($value) && ! method_exists($value, '__toString')) {
                return false;
            } elseif (is_array($value)) {
                return false;
            }

            return (string)$value;
        },
                $data ? $data->data() : []
        ));
        $keys = array_map(function ($key) {
            return "{{\${$key}}}";
        }, array_keys($data)
        );

        return implode('', array_map(function ($filepath) use (&$keys, &$data) {
            return str_replace($keys, $data, $this->content($filepath));
        }, $filepaths));
    }

    /**
     * Get content
     *
     * @param string $filepath
     * @return string
     */
    protected function content(string $filepath) : string
    {
        if (!isset($this->contents[$filepath])) {
            $this->contents[$filepath] = file_get_contents($filepath);
        }

        return (string)$this->contents[$filepath];
    }

}
