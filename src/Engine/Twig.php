<?php
/**
 * @copyright (c) 2017 Srcode
 */

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\Rule\Append;
use Srcoder\TemplateBridge\Content;
use Srcoder\TemplateBridge\Data;
use Srcoder\TemplateBridge\Exception\NotFoundException;

class Twig extends EngineAbstract
{

    /** @var \Twig_Environment */
    protected $_twig;

    public function __construct($paths = [], $rootPath = null, $options = [])
    {
        $loader = new \Twig_Loader_Filesystem(['.'], $rootPath);
        $this->_twig = new \Twig_Environment($loader, $options);

        parent::__construct($paths, $rootPath);

        $this->normalizer()
                ->addRule(new Append('.html.twig'));
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->_twig;
    }

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

        $twig = $this->getTwig();

        return array_reduce($filePaths, function(Content $content, $filename) use ($data, $content, $twig) {

            $rendered = $twig->load($filename)
                    ->render($data->data());

            $content->append($rendered);

            return $content;
        }, $content);
    }

}
