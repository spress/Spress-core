<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Core\ContentManager\Renderizer;

use Yosymfony\Spress\Core\Exception\AttributeValueException;

/**
 * Twig renderizer
 *
 * @author Victor Puertas <vpgugr@gmail.com>
 */
class TwigRenderizer implements RenderizerInterface
{
    protected $twig;
    protected $arrayLoader;
    protected $layoutExtension;

    /**
     * Construct
     *
     * @param \Twig_Environment  $twig
     * @param \Twig_Loader_Array $arrayLoader
     * @param array              $layoutExtension
     */
    public function __construct(\Twig_Environment $twig, \Twig_Loader_Array $arrayLoader, array $layoutExtension)
    {
        $this->twig = $twig;
        $this->arrayLoader = $arrayLoader;
        $this->layoutExtension = $layoutExtension;
    }

    /**
     * @inheritDoc
     */
    public function addLayout($name, $content, array $attributes = [])
    {
        $fullname = $this->getLayoutNameWithNamespace($name);
        $layout = $this->getLayoutAttribute($attributes, $name);

        if ($layout) {
            $fullLayout = $this->getLayoutWithExtension($layout, $name);

            if (isset($attributes['page']) === false) {
                $attributes['page'] = [];
            }

            $attributes['page']['content'] = $content;

            $content = sprintf('{%% extends "%s" %%}%s', $fullLayout, $content);
        }

        $this->arrayLoader->setTemplate($fullname, $content);
    }

    /**
     * @inheritDoc
     */
    public function addInclude($name, $content, array $attributes = [])
    {
        $this->arrayLoader->setTemplate($name, $content);
    }

    /**
     * @inheritDoc
     */
    public function renderBlocks($name, $content, array $attributes)
    {
        $this->arrayLoader->setTemplate('@dynamic/content', $content);

        return $this->twig->render('@dynamic/content', $attributes);
    }

    /**
     * @inheritDoc
     *
     * @throws \Yosymfony\Spress\Core\Exception\AttributeValueException if "layout" attribute has an invalid value.
     */
    public function renderPage($name, $content, array $attributes)
    {
        $layout = $this->getLayoutAttribute($attributes, $name);

        if ($layout) {
            $fullLayout = $this->getLayoutWithExtension($layout, $name);

            if (isset($attributes['page']) === false) {
                $attributes['page'] = [];
            }

            $attributes['page']['content'] = $content;

            $content = sprintf('{%% extends "%s" %%}', $fullLayout);
        }

        return $this->renderBlocks($name, $content, $attributes);
    }

    protected function getLayoutAttribute(array $attributes, $contentName)
    {
        if (isset($attributes['layout']) === false) {
            return false;
        }

        if (is_string($attributes['layout']) === false) {
            throw new AttributeValueException('Invalid value. Expected string.', 'layout', $contentName);
        }

        if (strlen($attributes['layout']) == 0) {
            throw new AttributeValueException('Invalid value. Expected a non-empty string.', 'layout', $contentName);
        }

        return $this->getLayoutNameWithNamespace($attributes['layout']);
    }

    protected function getLayoutNameWithNamespace($name)
    {
        return '@layout/'.$name;
    }

    protected function getLayoutWithExtension($layoutName, $contentName)
    {
        foreach ($this->layoutExtension as $extension) {
            $fullname = $layoutName.'.'.$extension;

            if ($this->arrayLoader->exists($fullname)) {
                return $fullname;
            }
        }

        if ($this->arrayLoader->exists($layoutName) === true) {
            return $layoutName;
        }

        throw new AttributeValueException(sprintf('Layout "%s" not found.', $name), 'layout', $contentName);
    }
}