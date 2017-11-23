<?php

namespace EavBundle\Tests\Service;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavValue;
use EavBundle\Service\EavValueTypeCast;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EavValueTypeCastTest
 */
class EavValueTypeCastTest extends KernelTestCase
{
    /**
     * @var EavValueTypeCast
     */
    protected $sut;

    /**
     * Prepares an ArrayCollection of the EavValue entities from provided assoc array
     *
     * @param array $values
     *
     * @return ArrayCollection
     */
    public function prepareValuesCollection(array $values)
    {
        $collection = new ArrayCollection();

        foreach ($values as $type => $value) {
            $eavAttribute = new EavAttribute();
            $eavAttribute
                ->setType($type)
                ->setName($type);

            $eavValue = new EavValue();
            $eavValue
                ->setAttribute($eavAttribute)
                ->setValue($value);

            $collection->set($type, $eavValue);
        }

        return $collection;
    }

    /**
     * Data provider for EavValue entity type cast test
     *
     * @return array
     */
    public function typeCastValueEntityDataProvider()
    {
        return [
            [
                'integer',
                '123',
                123,
            ],
            [
                'string',
                123,
                '123',
            ],
            [
                'boolean',
                1,
                true,
            ],
            [
                'float',
                '1.2',
                1.2,
            ],
            [
                'null',
                '',
                null,
            ],
            [
                'null',
                null,
                null,
            ],
        ];
    }

    /**
     * Tests that value of EavValue type casted correctly
     *
     * @param string $valueType
     * @param mixed  $valueBefore
     * @param mixed  $valueAfter
     *
     * @dataProvider typeCastValueEntityDataProvider
     */
    public function testTypeCastValueEntity($valueType, $valueBefore, $valueAfter)
    {
        $valuesCollection = $this->prepareValuesCollection([$valueType => $valueBefore]);

        $eavValue = $valuesCollection->get($valueType);

        $this->sut->typeCastValue($eavValue);

        $this->assertInstanceOf(EavValue::class, $eavValue);

        $value = $eavValue->getValue();

        $this->assertSame(gettype($valueAfter), gettype($value));
        $this->assertSame($valueAfter, $value);
    }

    /**
     * Tests the case when the attribute entity of EavValue object is not set
     */
    public function testUnsuccessfulTypeCasting()
    {
        $value    = 'some_value';
        $eavValue = new EavValue();

        $eavValue->setValue($value);

        $this->sut->typeCastValue($eavValue);

        $this->assertSame($value, $eavValue->getValue());
        $this->assertSame(gettype($value), gettype($eavValue->getValue()));
    }

    /**
     * Sets up a test
     */
    protected function setUp()
    {
        $this->sut = new EavValueTypeCast();
    }
}
