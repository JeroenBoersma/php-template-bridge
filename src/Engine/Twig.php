<?php
/**
 * @copyright (c) 2017 Srcode
 */

namespace Srcoder\TemplateBridge\Engine;

use Srcoder\Normalize\Rule\Append;
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
     * @param string $name
     * @return string
     */
    public function render(Data $data = null, string $name = null): string
    {
        return implode('',
                array_map(function($name) use ($data) {
                    return $this->getTwig()
                            ->render($name, $data ? $data->data() : []);
                }
                , $this->getFiles($name)
        ));
    }

    /**
     * Lookup a file
     *
     * @param string $name
     * @return string
     * @throws NotFoundException
     */
    public function lookup(string $name): string
    {
        $twig = $this->getTwig();
        $file = $this->normalize($name);

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
