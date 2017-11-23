<?php

namespace EavBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class EavKernelTestCase
 */
abstract class EavKernelTestCase extends KernelTestCase
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * Performs kernel initialisation
     */
    public static function setUpBeforeClass()
    {
        static::bootKernel();
        static::$container = static::$kernel->getContainer();
    }
}
