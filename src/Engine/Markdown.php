<?php
/**
 * @copyright (c) 2017 Srcode
 */

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Content;

class Markdown extends Plain
{

    /**
     * Return template
     *
     * @param Data $data
     * @param string $singleFilename
     * @return Content
     */
    public function render(Data $data = null, string $singleFilename = null) : Content
    {
        $content = parent::render($data, $singleFilename);

        $parsedown = new \Parsedown();
        return new Content($parsedown->text($content->content()));
    }

}
