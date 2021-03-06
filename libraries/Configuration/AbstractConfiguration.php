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

namespace FlameCore\Infernum\Configuration;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * The abstract Configuration class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractConfiguration
{
    /**
     * @var array
     */
    private $files = array();

    /**
     * @var \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private $treeBuilder;

    /**
     * @var \Symfony\Component\Config\Definition\Processor
     */
    private $processor;

    /**
     * @param string|array $files
     */
    public function __construct($files)
    {
        $this->files = (array) $files;

        $treeBuilder = $this->getDefinitionTree();

        if (!$treeBuilder instanceof TreeBuilder) {
            throw new \UnexpectedValueException(sprintf('%s::getDefinitionTree() does not provide a Symfony\Component\Config\Definition\Builder\TreeBuilder object.', get_class($this)));
        }

        $this->treeBuilder = $treeBuilder;
        $this->processor = new Processor();
    }

    /**
     * @return array
     */
    public function load()
    {
        $configs = array();

        foreach ($this->files as $file) {
            if (!file_exists($file)) {
                throw new \LogicException(sprintf('File "%s" does not exist.', $file));
            }

            $configs[] = $this->parseConfigFile($file);
        }

        $tree = $this->treeBuilder->buildTree();

        return $this->processor->process($tree, $configs);
    }

    /**
     * @param string $file
     * @return array
     */
    protected function parseConfigFile($file)
    {
        $yaml = new Parser();

        return $yaml->parse(file_get_contents($file));
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    abstract protected function getDefinitionTree();
}
