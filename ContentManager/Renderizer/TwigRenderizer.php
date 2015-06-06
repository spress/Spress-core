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
     * @param array              $layoutExtension Extension availables for layouts. e.g: "html", "html.twig", "twig"
     */
    public function __construct(\Twig_Environment $twig, \Twig_Loader_Array $arrayLoader, array $layoutExtension)
    {
        $this->arrayLoaderOrg = $arrayLoader;
        $this->twig = $twig;
        $this->layoutExtension = $layoutExtension;
        $this->arrayLoader = $arrayLoader;
    }

    /**
     * Add a new layout
     *
     * @param string $name       The name of the layout. e.g: id, path...
     * @param string $content    The content of the layout
     * @param array  $attributes The attributes of the layout.
     *                           "layout" attribute has a special meaning.
     *
     * @throws \RuntimeException if a previous layout exists with the same name
     */
    public function addLayout($name, $content, array $attributes = [])
    {
        $fullname = $this->getLayoutNameWithNamespace($name);

        if ($this->arrayLoader->exists($fullname) === true) {
            throw new \RuntimeException(sprintf('A previous layout exists with the same name: "%s".', $name));
        }

        $layout = $this->getLayoutAttribute($attributes, $name);

        if ($layout) {
            $fullLayout = $this->getLayoutWithExtension($layout, $name);

            $content = sprintf('{%% extends "%s" %%}%s', $fullLayout, $content);
        }

        $this->arrayLoader->setTemplate($fullname, $content);
    }

    /**
     * @inheritDoc
     *
     * @throws \RuntimeException if a previous include exists with the same name
     */
    public function addInclude($name, $content, array $attributes = [])
    {
        if ($this->arrayLoader->exists($name) === true) {
            throw new \RuntimeException(sprintf('A previous include exists with the same name: "%s".', $name));
        }

        $this->arrayLoader->setTemplate($name, $content);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->twig->clearCacheFiles();
        $this->twig->clearTemplateCache();
    }

    /**
     * Render a blocks of content (layout NOT included)
     *
     * @param string $name       The path of the item
     * @param string $content    The content
     * @param array  $attributes The attributes for using inside the content
     *
     * @return string The block rendered
     */
    public function renderBlocks($name, $content, array $attributes)
    {
        $this->arrayLoader->setTemplate('@dynamic/content', $content);

        return $this->twig->render('@dynamic/content', $attributes);
    }

    /**
     * Render a page completely (layout included). The value of $content
     * param will be placed at "page.content" attribute.
     *
     * @param  string $name           The path of the item.
     * @param  string $content        The page content.
     * @param  string $layoutName     The layout name.
     * @param  array  $siteAttributes The attributes for using inside the content.
     *                                "layout" attribute has a special meaning.
     * @return string The page rendered
     *
     * @throws \Yosymfony\Spress\Core\Exception\AttributeValueException if "layout" attribute has an invalid value
     *                                                                  or layout not found
     */
    public function renderPage($name, $content, $layoutName, array $siteAttributes)
    {
        if ($layoutName) {
            $layout = $this->getLayoutNameWithNamespace($layoutName);
            $fullLayout = $this->getLayoutWithExtension($layout, $name);

            if (isset($siteAttributes['page']) === false) {
                $siteAttributes['page'] = [];
            }

            $siteAttributes['page']['content'] = $content;

            $content = sprintf('{%% extends "%s" %%}', $fullLayout);
        }

        return $this->renderBlocks($name, $content, $siteAttributes);
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

        throw new AttributeValueException(sprintf('Layout "%s" not found.', $layoutName), 'layout', $contentName);
    }
}
