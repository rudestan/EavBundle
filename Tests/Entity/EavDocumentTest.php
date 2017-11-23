<?php

namespace EavBundle\Tests\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavPromisedAttribute;
use EavBundle\Entity\EavType;
use EavBundle\Entity\EavValue;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class EavDocumentTest
 */
class EavDocumentTest extends KernelTestCase
{
    /**
     * Data provider for hydrateValues method test
     *
     * @return array
     */
    public function hydrateValuesDataProvider()
    {
        return [
            [
                [
                    'val1' => 1,
                    'val2' => 'text',
                    'val3' => false,
                    'val4' => 2,
                    'val5' => 3.5,
                ],
                [],
            ],
        ];
    }

    /**
     * Tests hydrateValues method
     *
     * @param array $values
     *
     * @dataProvider hydrateValuesDataProvider
     */
    public function testHydrateValues(array $values)
    {
        $eavDocument = new EavDocument();
        $eavDocument->setValues(null);
        $eavDocument->hydrateValues($values);

        $valuesCollection = $eavDocument->getValues();

        $this->assertInstanceOf(ArrayCollection::class, $valuesCollection);
        $this->assertCount(count($values), $valuesCollection);
    }

    /**
     * Data provider for values related tests
     *
     * @return array
     */
    public function valuesDataProvider()
    {
        return [
            'new value'                     => [
                'mynew_value',
                'new value',
                2,
            ],
            'existing value'                => [
                'myvalue_id',
                567,
                1,
            ],
            'exsiting value with attribute' => [
                'myvalue_id',
                'new value',
                1,
            ],
            'check name of attribute'       => [
                '1',
                123,
                2,
            ],
        ];
    }

    /**
     * Tests set/get Value methods
     *
     * @param string $setName
     * @param mixed  $value
     * @param int    $count
     *
     * @dataProvider valuesDataProvider
     */
    public function testSetGetValue($setName, $value, $count)
    {
        $eavAttribute = null;
        $eavDocument  = new EavDocument();
        $eavDocument->hydrateValues(['myvalue_id' => 123]);
        $eavDocument->setValue($setName, $value);

        $eavValue = $eavDocument->getValue($setName);

        $this->assertInstanceOf(EavValue::class, $eavValue);
        $this->assertSame($value, $eavValue->getValue());
        $this->assertInstanceOf(EavPromisedAttribute::class, $eavValue->getAttribute());
        $this->assertCount($count, $eavDocument->getValues());
    }

    /**
     * Tests that getValue method returns null for non-existent value
     */
    public function testGetValueNull()
    {
        $eavDocument = new EavDocument();
        $value = $eavDocument->getValue('non_existent');

        $this->assertNull($value);
    }

    /**
     * Tests removeValue method
     *
     * @param string $name
     * @param mixed  $value
     *
     * @dataProvider valuesDataProvider
     */
    public function testRemoveValue($name, $value)
    {
        $eavDocument = new EavDocument();

        $eavDocument->setValue($name, $value);

        $eavValue = $eavDocument->getValue($name);
        $removed  = $eavDocument->removeValue($name);

        $this->assertInstanceOf(EavValue::class, $removed);
        $this->assertSame($removed, $eavValue);

        $removed = $eavDocument->removeValue($name);

        $this->assertNull($removed);
    }

    /**
     * Data provider for hasValue method test
     *
     * @return array
     */
    public function hasValueDataProvider()
    {
        return [
            [
                'myvalue1',
                'some text',
                'myvalue1',
                true,
            ],
            [
                'myvalue1',
                'some text',
                'myvalue2',
                false,
            ],
            [
                null,
                'some text',
                'myvalue3',
                false,
            ],
        ];
    }

    /**
     * Tests hasValue method
     *
     * @param string $name
     * @param mixed  $value
     * @param string $searchName
     * @param bool   $expectedResult
     *
     * @dataProvider hasValueDataProvider
     */
    public function testHasValue($name, $value, $searchName, $expectedResult)
    {
        $eavDocument = new EavDocument();

        $attribute = null;

        if ($name) {
            $attribute = new EavPromisedAttribute();
            $attribute->setName($name);
        }
        $eavValue = new EavValue();
        $eavValue
            ->setValue($value)
            ->setAttribute($attribute);

        $eavDocument->setValues(new ArrayCollection([$eavValue]));

        $hasValue = $eavDocument->hasValue($searchName);

        $this->assertEquals($hasValue, $expectedResult);
    }

    /**
     * Data provider for the attributes related tests
     *
     * @return array
     */
    public function attributesDataProvider()
    {
        return [
            [
                [
                    'value1' => 123,
                    'value2' => 'string',
                ],
                ['value1'],
                ['value1'],
                [
                    'value1' => 123,
                ],
            ],
            [
                [
                    'value1' => 456,
                    'value2' => 'text',
                    'value3' => true,
                ],
                ['value1', 'value3'],
                ['value1', 'value3'],
                [
                    'value1' => 456,
                    'value3' => true,
                ],
            ],
            [
                [
                    'value1' => 'text',
                    'value2' => 789,
                    'value3' => false,
                ],
                ['value1', 'value2', 'value3'],
                ['value1', 'value2', 'value3'],
                [
                    'value1' => 'text',
                    'value2' => 789,
                    'value3' => false,
                ],
            ],
        ];
    }

    /**
     * Tests getAttributeNames method
     *
     * @param array $hydrateValues
     * @param array $attributes
     * @param array $expectedNames
     *
     * @dataProvider attributesDataProvider
     */
    public function testGetAttributeNames(array $hydrateValues, array $attributes, array $expectedNames)
    {
        $eavDocument = new EavDocument();
        $eavDocument->hydrateValues($hydrateValues);
        $this->setAttributes($eavDocument, $attributes);

        $attributeNames = $eavDocument->getAttributeNames();

        $this->assertSame($expectedNames, $attributeNames);
    }

    /**
     * Data provider for getter/setter methods of EavDocument entity
     *
     * @return array
     */
    public function gettersSettersDataProvider()
    {
        $eavType    = new EavType();
        $values     = new ArrayCollection();
        $violations = new ConstraintViolationList();

        return [
            [null, 'getId', null, null],
            ['setType', 'getType', $eavType, $eavType],
            ['setPath', 'getPath', '/path/to/file/file.pdf', '/path/to/file/file.pdf'],
            ['setValues', 'getValues', $values, $values],
            ['setLastViolations', 'getLastViolations', $violations, $violations],
        ];
    }

    /**
     * Tests getter/setter methods of the EavDocument entity
     *
     * @param string $setMethod
     * @param string $getMethod
     * @param mixed  $setValue
     * @param mixed  $expectedValue
     *
     * @dataProvider gettersSettersDataProvider
     */
    public function testGettersSetters($setMethod, $getMethod, $setValue, $expectedValue)
    {
        $eavDocument = new EavDocument();

        if ($setMethod) {
            $eavDocument->{$setMethod}($setValue);
        }

        $value = $eavDocument->{$getMethod}();

        $this->assertSame($expectedValue, $value);
    }

    /**
     * Tests setting of createdAt and modifedAt parameters that triggered in onPrePersist and
     * onPreUpdate methods
     */
    public function testOnPreUpdatedOnPrePersist()
    {
        $eavDocument = new EavDocument();

        $this->assertNull($eavDocument->getCreatedAt());
        $this->assertNull($eavDocument->getModifiedAt());

        $eavDocument->onPrePersist();
        $eavDocument->onPreUpdate();

        $createdAt = $eavDocument->getCreatedAt();
        $this->assertInstanceOf(DateTime::class, $createdAt);

        $modifiedAt = $eavDocument->getModifiedAt();
        $this->assertInstanceOf(DateTime::class, $modifiedAt);
    }

    /**
     * Tests getValuesForSerialization method
     *
     * @param array $hydrateValues
     * @param array $attributes
     * @param array $expectedNames
     * @param array $expectedValues
     *
     * @dataProvider attributesDataProvider
     */
    public function testGetValuesForSerialization(
        array $hydrateValues,
        array $attributes,
        array $expectedNames,
        array $expectedValues
    ) {
        $typeAttrs = array_fill_keys($attributes, []);
        $eavType   = new EavType();
        $eavType->setAttributes($typeAttrs);

        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);

        $values = $eavDocument->getValuesForSerialization();

        $this->assertEmpty($values);

        $eavDocument->hydrateValues($hydrateValues);
        $this->setAttributes($eavDocument, $attributes);

        $values = $eavDocument->getValuesForSerialization();

        foreach ($expectedNames as $key) {
            $this->assertArrayHasKey($key, $values);
        }

        $this->assertSame($expectedValues, $values);
    }

    /**
     * Data provider for values for serialization failures test
     *
     * @return array
     */
    public function valuesForSerializationFailuresDataProvider()
    {
        $eavType1 = new EavType();

        $eavType2 = new EavType();
        $eavType2->setAttributes(['some_attribute' => ['required' => true]]);

        return [
            [
                null,
            ],
            [
                $eavType1,
            ],
            [
                $eavType2,
            ],
        ];
    }

    /**
     * Tests edge cases of getValuesForSerialization method
     *
     * @param null|EavType $eavType
     *
     * @dataProvider valuesForSerializationFailuresDataProvider
     */
    public function testGetValuesForSerializationFailures($eavType)
    {
        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);
        $eavDocument->setValues(new ArrayCollection(['some_value']));

        $values = $eavDocument->getValuesForSerialization();

        $this->assertEmpty($values);
    }

    /**
     * Tests successful setRawEavValue method call
     */
    public function testSetRawAttributeSuccess()
    {
        $eavDocument = new EavDocument();

        $eavAttribute = new EavPromisedAttribute();
        $eavAttribute->setName('attribute_name');

        $eavValue = new EavValue();
        $eavValue->setAttribute($eavAttribute);

        $eavDocument->setRawEavValue($eavValue);

        $this->assertCount(1, $eavDocument->getValues());
        $this->assertSame($eavValue, $eavDocument->getValue('attribute_name'));
    }

    /**
     * Tests failure of setRawEavValue method in case EavValue has no attribute
     *
     * @expectedException \EavBundle\Exception\EavAttributeNotSetException
     */
    public function testSetRawAttributeFailed()
    {
        $eavDocument = new EavDocument();
        $eavValue    = new EavValue();

        $eavDocument->setRawEavValue($eavValue);
    }

    /**
     * Returns new EavAttribute object
     *
     * @param string     $type
     * @param string     $name
     * @param array|null $validators
     *
     * @return EavAttribute
     */
    protected function getNewAttribute($type, $name, $validators)
    {
        $eavAttribute = new EavAttribute();
        $eavAttribute
            ->setType($type)
            ->setName($name)
            ->setValidators($validators);

        return $eavAttribute;
    }

    /**
     * Sets attributes for EavDocument values
     *
     * @param EavDocument $eavDocument
     * @param array       $attributes
     */
    protected function setAttributes($eavDocument, array $attributes)
    {
        foreach ($attributes as $attributeName) {
            $eavValue = $eavDocument->getValue($attributeName);

            $this->assertInstanceOf(EavValue::class, $eavValue);

            $eavAttribute = $this->getNewAttribute('string', $attributeName, []);

            $eavValue->setAttribute($eavAttribute);
        }
    }
}
