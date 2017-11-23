<?php

namespace EavBundle\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

/**
 * Class EavDbTestCase
 */
abstract class EavDbTestCase extends EavKernelTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Performs kernel initialisation and loads fixtures
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::loadFixtures();
    }

    /**
     * Sets up a test
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = static::$container->get('doctrine')->getManager();
    }

    /**
     * Cleans up after test
     */
    protected function tearDown()
    {
        unset($this->em);
        parent::tearDown();
    }

    /**
     * Loads all fixtures defined for a given test class
     */
    protected static function loadFixtures()
    {
        $entityManager = static::$container->get('doctrine')->getManager();
        $loader        = new Loader();

        static::addFixtures($loader, static::getFixtures());

        $purger   = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * Provides an ArrayCollection of Fixtures instances that should be loaded for the test.
     * Override it in your test to specify which fixtures to load.
     *
     * @return ArrayCollection
     */
    protected static function getFixtures()
    {
        return new ArrayCollection([]);
    }

    /**
     * Adds provided fixtures to the loader
     *
     * @param Loader          $loader
     * @param ArrayCollection $fixtures
     */
    protected static function addFixtures($loader, $fixtures)
    {
        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }
    }
}
