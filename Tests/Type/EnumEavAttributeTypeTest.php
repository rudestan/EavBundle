<?php

namespace EavBundle\Tests\Type;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use EavBundle\Type\EnumEavAttributeType;

/**
 * Class EnumEavAttributeTypeTest
 */
class EnumEavAttributeTypeTest extends KernelTestCase
{
    /**
     * @var Type
     */
    protected $type;

    /**
     * @var AbstractPlatform|ObjectProphecy
     */
    protected $platformProphecy;

    /**
     * Tests the getSQLDeclaration method
     */
    public function testGetSQLDeclaration()
    {
        $sql = $this->type->getSQLDeclaration([], $this->platformProphecy->reveal());
        $this->assertRegExp('#ENUM\([a-z_\',\s]+\)#', $sql);
    }

    /**
     * Tests the getName method
     */
    public function testGetName()
    {
        $this->assertSame($this->type->getName(), 'enumEavAttributeType');
    }

    /**
     * Tests the requireSQLCommentHint method
     */
    public function testRequireSQLCommentHint()
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platformProphecy->reveal()));
    }

    /**
     * Tests the convertToDatabaseValue method
     */
    public function testConvertToDatabaseValue()
    {
        $value = $this->type->convertToDatabaseValue(
            EnumEavAttributeType::TYPE_INTEGER,
            $this->platformProphecy->reveal()
        );

        $this->assertSame(EnumEavAttributeType::TYPE_INTEGER, $value);
    }

    /**
     * Tests the convertToDatabaseValue method
     *
     * @expectedException \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function testConvertToDatabaseValueException()
    {
        $this->type->convertToDatabaseValue('xpto_wrong_type', $this->platformProphecy->reveal());
    }

    /**
     * Sets up a test
     */
    protected function setUp()
    {
        $this->type             = Type::getType('enumEavAttributeType');
        $this->platformProphecy = $this->prophesize(AbstractPlatform::class);
    }
}
