<?php
/**
 * @copyright (c) 2017 Srcode
 */

declare(strict_types=1);

namespace Srcoder\TemplateBridge;

class Content
{

    /** @var mixed */
    protected $content;

    /** @var bool */
    protected $isReturn = false;

    /**
     * Content constructor.
     * @param $content
     * @param bool $isReturn
     */
    public function __construct($content, bool $isReturn = false)
    {
        $this->content = $content;
        $this->isReturn = $isReturn;
    }

    /**
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     *
     * @return string
     */
    public function toString() : string
    {
        return (string)$this->content();
    }

    /**
     *
     * @return mixed
     */
    public function content()
    {
        return $this->content;
    }

    /**
     *
     * @param $content
     * @return Content
     */
    public function append(string $content)
    {
        return new self($this->content . $content, false);
    }

    /**
     * Was content returned in include
     *
     * @return bool
     */
    public function isReturn() : bool
    {
        return $this->isReturn;
    }

}
