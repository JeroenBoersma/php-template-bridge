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
        $this->normalizerInit()
                ->addRule(new Append('.html.twig'));

        $loader = new \Twig_Loader_Filesystem($paths, $rootPath);
        $this->_twig = new \Twig_Environment($loader, $options);
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
     * @return string
     */
    public function render(Data $data = null, string $singleFilename = null): Content
    {
        $content = new Content('');
        $filePaths = $this->getFilePaths($singleFilename);

        return array_reduce($filePaths, function(Content $content, $filename) use ($data, $content) {
            $rendered = $this->getTwig()
                    ->render($filename, $data->data());

            $content->append($rendered);

            return $content;
        }, $content);
    }

    /**
     * Lookup a file
     *
     * @param string $filename
     * @return string
     * @throws NotFoundException
     */
    public function lookup(string $filename): string
    {
        $twig = $this->getTwig();
        $file = $this->normalize($filename);

        try {
            $twig->load($file);
        } catch (\Twig_Error $error) {
            throw new NotFoundException($error->getMessage(), $error->getCode(), $error);
        }

        return $file;
    }

    /**
     * Add a path for lookup
     *
     * @param string $path
     * @return EngineInterface
     */
    public function appendPath(string $path): EngineInterface
    {
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $this->getTwig()
                ->getLoader();
        $loader->addPath($path);

        return $this;
    }

    /**
     * Prepend a path for lookup
     *
     * @param string $path
     * @return EngineInterface
     */
    public function prependPath(string $path): EngineInterface
    {
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $this->getTwig()
                ->getLoader();
        $loader->prependPath($path);

        return $this;
    }

}
