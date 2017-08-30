<?php
/**
 * @copyright (c) 2017 Srcode
 */

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Content;

class Plain extends EngineAbstract
{

    /** @var array */
    protected $contents = [];

    /**
     * Return template
     *
     * @param Data $data
     * @param string $singleFilename
     * @return Content
     */
    public function render(Data $data = null, string $singleFilename = null) : Content
    {
        $content = new Content('');
        $filePaths = $this->getFilePaths($singleFilename);

        if (empty($filePaths)) {
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
        }, array_keys($data));

        return array_reduce($filePaths, function(Content $content, $filename) use (&$keys, &$data) {

            $content->append(str_replace($keys, $data, $this->content($filename)));

            return $content;
        }, $content);
    }

    /**
     * Get content
     *
     * @param string $filename
     * @return string
     */
    protected function content(string $filename) : string
    {
        if (!isset($this->contents[$filename])) {
            $this->contents[$filename] = file_get_contents($filename);
        }

        return (string)$this->contents[$filename];
    }

}
