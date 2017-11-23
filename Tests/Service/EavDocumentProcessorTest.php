<?php

namespace EavBundle\Tests\Service;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavPromisedAttribute;
use EavBundle\Entity\EavType;
use EavBundle\Entity\EavValue;
use EavBundle\Repository\EavAttributeRepository;
use EavBundle\Service\EavDocumentProcessor;
use EavBundle\Tests\EavKernelTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class EavDocumentProcessorTest
 */
class EavDocumentProcessorTest extends EavKernelTestCase
{
    /**
     * @var EavDocumentProcessor
     */
    protected $sut;

    /**
     * Tests that hydrateAttributes method fails with exception in case certain attribute does not exist in
     * database
     *
     * @expectedException \EavBundle\Exception\EavPromisedAttributeNotSetException
     */
    public function testHydrateAttributesExceptionPromisedAttribute()
    {
        $value = new EavValue();

        $attrRepositoryProphecy = $this->prophesize(EavAttributeRepository::class);

        $this->sut->hydrateValueAttribute($value, $attrRepositoryProphecy->reveal());
    }

    /**
     * Tests that hydrateAttributes method fails with exception in case certain attribute does not exist in
     * database
     *
     * @expectedException \EavBundle\Exception\EavAttributeNotFoundException
     */
    public function testHydrateAttributeException()
    {
        $eavPromisedAttribute = new EavPromisedAttribute();
        $eavPromisedAttribute->setName('non_existent');

        $eavValue = new EavValue();
        $eavValue->setAttribute($eavPromisedAttribute);

        $attrRepositoryProphecy = $this->prophesize(EavAttributeRepository::class);
        $attrRepositoryProphecy->findOneBy(['name' => 'non_existent'])
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $this->sut->hydrateValueAttribute($eavValue, $attrRepositoryProphecy->reveal());
    }

    /**
     * Tests that hydrateAttributes method successfully sets the proper attribute to the value
     */
    public function testHydrateAttributesSuccess()
    {
        $attribute = new EavAttribute();
        $attribute->setType('string')->setName('valid_attribute');

        $promissedAttribute = new EavPromisedAttribute();
        $promissedAttribute->setName('valid_attribute');

        $val1 = new EavValue();
        $val1->setAttribute($promissedAttribute);

        $attrRepositoryProphecy = $this->prophesize(EavAttributeRepository::class);
        $attrRepositoryProphecy->findOneBy(['name' => 'valid_attribute'])
            ->shouldBeCalledTimes(1)
            ->willReturn($attribute);

        $this->sut->hydrateValueAttribute($val1, $attrRepositoryProphecy->reveal());

        $attr = $val1->getAttribute();

        $this->assertInstanceOf(EavAttribute::class, $attr);
        $this->assertSame('valid_attribute', $attr->getName());
    }

    /**
     * Process Entity data provider
     *
     * @return array
     */
    public function validateCompleteDocumentDataProvider()
    {
        return [
            [
                [
                    'int_attr'    => 123,
                    'string_attr' => 'some text',
                    'bool_attr'   => true,
                    'float_attr'  => 2.4,
                    'null_attr'   => null,
                ],
                [
                    'int_attr'    => 'integer',
                    'string_attr' => 'string',
                    'bool_attr'   => 'boolean',
                    'float_attr'  => 'double',
                    'null_attr'   => 'string',
                ],
                [
                    'int_attr'    => true,
                    'string_attr' => true,
                    'bool_attr'   => true,
                    'float_attr'  => true,
                    'null_attr'   => true,
                ],
                0,
            ],
            [
                [
                    'wrong_int_attr'    => '24235',
                    'wrong_string_attr' => 123,
                    'wrong_bool_attr'   => 'true',
                    'wrong_float_attr'  => true,
                ],
                [
                    'wrong_int_attr'    => 'integer',
                    'wrong_string_attr' => 'string',
                    'wrong_bool_attr'   => 'boolean',
                    'wrong_float_attr'  => 'double',
                ],
                [
                    'wrong_int_attr'    => true,
                    'wrong_string_attr' => true,
                    'wrong_bool_attr'   => true,
                    'wrong_float_attr'  => true,
                ],
                4,
            ],
        ];
    }

    /**
     * Returns attribute repository prophecy
     *
     * @param array $types
     *
     * @return ObjectProphecy
     */
    public function getAttrRepositoryProphecy(array $types)
    {
        $attrRepositoryProphecy = $this->prophesize(EavAttributeRepository::class);

        foreach ($types as $name => $type) {
            $attribute = new EavAttribute();
            $attribute
                ->setName($name)
                ->setType($type);

            $attrRepositoryProphecy
                ->findOneBy(['name' => $name])
                ->shouldBeCalledTimes(1)
                ->willReturn($attribute);
        }

        return $attrRepositoryProphecy;
    }

    /**
     * Tests processing of the EavDocument entity
     *
     * @param array $values
     * @param array $types
     * @param array $attrList
     * @param int   $violationCount
     *
     * @dataProvider validateCompleteDocumentDataProvider
     */
    public function testValidateCompleteDocument(array $values, array $types, array $attrList, $violationCount)
    {
        $attrRepositoryProphecy = $this->getAttrRepositoryProphecy($types);

        $type = $this->getNewType('new_type', 'NewType', $attrList);

        $this->assertEquals(null, $type->getId());
        $this->assertEquals('new_type', $type->getAlias());
        $this->assertEquals('NewType', $type->getName());
        $this->assertEquals($attrList, $type->getAttributes());

        $document = new EavDocument();
        $document->setType($type);
        $document->hydrateValues($values);

        $hydratedValues = $document->getValues();
        $this->assertCount(count($values), $hydratedValues);

        $actualViolations = $this->sut->validateCompleteDocument($document, $attrRepositoryProphecy->reveal());
        $this->assertInstanceOf(ConstraintViolationListInterface::class, $actualViolations);
        $this->assertCount($violationCount, $actualViolations);

        $valuesForSerialization = $document->getValuesForSerialization();

        $this->assertSame($values, $valuesForSerialization);
    }

    /**
     * Tests failure validation of the document entities
     */
    public function testValidateDocumentEntitiesFailed()
    {
        $attrRepositoryProphecy = $this->getAttrRepositoryProphecy([]);

        $eavType = $this->getNewType('new_type', 'NewType', ['customerId' => ['required' => true]]);

        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);

        $documentEntities = [
            $eavDocument,
            new EavAttribute(),
        ];

        $areValid = $this->sut->validateDocumentEntities($documentEntities, $attrRepositoryProphecy->reveal());

        $this->assertFalse($areValid);
        $this->assertCount(1, $eavDocument->getLastViolations());
        $this->assertContains(
            'The required param "customerId" is not set!',
            (string) $eavDocument->getLastViolations()
        );
    }

    /**
     * Test successful validation of document entities
     */
    public function testValidateDocumentEntitiesSuccess()
    {
        $attrRepositoryProphecy = $this->getAttrRepositoryProphecy([]);

        $eavAttribute = new EavAttribute();
        $eavAttribute->setType('integer');
        $eavAttribute->setName('customerId');

        $eavValue = new EavValue();
        $eavValue->setValue(123);
        $eavValue->setAttribute($eavAttribute);

        $eavType = $this->getNewType('new_type', 'NewType', ['customerId' => ['required' => true]]);

        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);
        $eavDocument->setRawEavValue($eavValue);

        $documentEntities = [
            $eavDocument,
            new EavAttribute(),
        ];

        $areValid = $this->sut->validateDocumentEntities($documentEntities, $attrRepositoryProphecy->reveal());

        $this->assertTrue($areValid);
        $this->assertCount(0, $eavDocument->getLastViolations());
    }

    /**
     * Tests isEntityProcessed method
     */
    public function testIsEntityProcessed()
    {
        $attrRepositoryProphecy = $this->getAttrRepositoryProphecy([
            'someAttr1' => 'integer',
            'someAttr2' => 'integer',
        ]);

        $eavType = $this->getNewType('new_type', 'NewType', []);

        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);
        $eavDocument->hydrateValues([
            'someAttr1' => 124,
            'someAttr2' => 345,
        ]);

        $this->assertFalse($this->sut->isEntityProcessed($eavDocument));
        $this->sut->validateCompleteDocument($eavDocument, $attrRepositoryProphecy->reveal());
        $this->assertTrue($this->sut->isEntityProcessed($eavDocument));

        $eavDocument->setValue('someAttr1', 456);
        $this->assertFalse($this->sut->isEntityProcessed($eavDocument));

        $eavDocument->setValue('someAttr1', 124);
        $this->assertTrue($this->sut->isEntityProcessed($eavDocument));

        $eavDocument->setValue('someAttr1', '124');
        $this->assertFalse($this->sut->isEntityProcessed($eavDocument));

        $this->sut->resetProcessedEntities();
        $this->assertFalse($this->sut->isEntityProcessed($eavDocument));
    }

    /**
     * Sets up a test
     */
    protected function setUp()
    {
        $validator = static::$container->get('validator');
        $this->sut = new EavDocumentProcessor($validator);
    }

    /**
     * Returns new EavType object
     *
     * @param string $alias
     * @param string $name
     * @param array  $attributes
     *
     * @return EavType
     */
    protected function getNewType($alias, $name, $attributes)
    {
        $type = new EavType();
        $type
            ->setAlias($alias)
            ->setName($name)
            ->setAttributes($attributes)
            ->setValidators([]);

        return $type;
    }
}
