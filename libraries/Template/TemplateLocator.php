<?php
/**
 * Infernum
 * Copyright (C) 2015 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  http://opensource.org/licenses/ISC ISC License
 */

namespace FlameCore\Infernum\Template;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template\BadNameException;
use FlameCore\Infernum\Template\NotFoundException;

/**
 * Locator for template engines
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TemplateLocator
{
    /**
     * @var \FlameCore\Infernum\Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $namespaces = array();

    /**
     * @param \FlameCore\Infernum\Application $app
     */
    final public function __construct(Application $app)
    {
        $this->setNamespace('global', $app->getTemplatePath());

        $this->app = $app;
    }

    /**
     * Returns the local template path
     *
     * @return string|bool
     */
    final public function getLocalPath()
    {
        return $this->app->getTemplatePath(true);
    }

    /**
     * Checks whether or not the given namespace is defined
     *
     * @param string $namespace The namespace
     * @return bool
     */
    final public function isNamespaceDefined($namespace)
    {
        return isset($this->namespaces[$namespace]);
    }

    /**
     * Returns the template path of the given namespace
     *
     * @param string $namespace The namespace
     * @return string|bool
     */
    final public function getNamespace($namespace)
    {
        return isset($this->namespaces[$namespace]) ? $this->namespaces[$namespace] : false;
    }

    /**
     * Assigns a namespace with given template path
     *
     * @param string $namespace The namespace
     * @param string $path The template path of the namespace
     */
    final public function setNamespace($namespace, $path)
    {
        $this->namespaces[$namespace] = $path;
    }

    /**
     * Searches a template and returns its full filename
     *
     * @param string $template The name of the template
     * @return string
     * @throws \FlameCore\Infernum\Template\NotFoundException
     * @throws \FlameCore\Infernum\Template\BadNameException
     */
    public function locate($template)
    {
        $template = preg_replace('#/{2,}#', '/', strtr((string) $template, '\\', '/'));

        if (strpos($template, "\0") !== false) {
            throw new BadNameException('A template name cannot contain NUL bytes.');
        }

        $template = ltrim($template, '/');
        $parts = explode('/', $template);
        $level = 0;
        foreach ($parts as $part) {
            if ($part === '..') {
                --$level;
            } elseif ($part !== '.') {
                ++$level;
            }

            if ($level < 0) {
                throw new BadNameException(sprintf('Looks like you try to load a template outside configured directories. (%s)', $template));
            }
        }

        if ($template[0] == '@') {
            if (false === $pos = strpos($template, '/')) {
                throw new BadNameException(sprintf('Malformed namespaced template name "%s". (expecting "@namespace/template_name")', $template));
            }

            $namespace = substr($template, 1, $pos - 1);

            if (!$this->isNamespaceDefined($namespace)) {
                throw new BadNameException(sprintf('Cannot find template "%s": The template namespace "%s" is not defined.', $template, $namespace));
            }

            $name = substr($template, $pos + 1);
            $path = $this->getNamespace($namespace);

            $filename = "$path/$name.twig";
        } else {
            $path = $this->getLocalPath();

            if (!$path) {
                throw new BadNameException(sprintf('Cannot find template "%s": There is no local template path defined.', $template));
            }

            $filename = "$path/$template.twig";
        }

        if (!file_exists($filename)) {
            throw new NotFoundException(sprintf('Unable to find template "%s". (looked into: %s)', $template, $path));
        }

        return $filename;
    }
}
