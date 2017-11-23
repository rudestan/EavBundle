<?php

namespace EavBundle\Tests\Validator\Constraints;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavType;
use EavBundle\Validator\Constraints\EavDocumentConstraint;
use EavBundle\Validator\Constraints\EavDocumentConstraintValidator;

/**
 * Class EavDocumentConstraintValidatorTest
 */
class EavDocumentConstraintValidatorTest extends EavAbstractConstraintValidatorTest
{
    /**
     * Data provider for EavDocument validation test
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'empty type attributes'  => [
                null,
                [],
                0,
                null,
            ],
            'unsupported attributes' => [
                [
                    'attribute1' => ['required' => false],
                    'attribute2' => ['required' => false],
                    'attribute3' => ['required' => false],
                ],
                [
                    'attribute1' => ['string', 'text'],
                    'attribute2' => ['integer', 123],
                    'attribute3' => ['string', 'something'],
                    'attribute4' => ['boolean', true],
                ],
                1,
                'Object(EavBundle\Entity\EavDocument).attributes',
            ],
            'required attributes'    => [
                [
                    'attribute1' => ['required' => true],
                    'attribute2' => ['required' => false],
                ],
                [
                    'attribute2' => ['integer', 123],
                ],
                1,
                'Object(EavBundle\Entity\EavDocument).attribute1',
            ],
            'conditional not required' => [
                [
                    'customerDocumentType' => ['required' => true],
                    'documentReference'    => ['required' => false],
                ],
                [
                    'customerDocumentType' => ['string', 'customer_invoice'],
                ],
                1,
                'The conditional ("customerDocumentType != other") required param "documentReference" is not set!',
                [
                    'EavDocumentConditionalNotRequiredConstraint' => [
                        'checkAttribute'    => 'customerDocumentType',
                        'value'             => 'other',
                        'requiredAttribute' => 'documentReference',
                    ],
                ],
            ],
            'conditional not required attribute null' => [
                [
                    'customerDocumentType' => ['required' => true],
                    'documentReference'    => ['required' => false],
                ],
                [
                    'customerDocumentType' => ['string', 'customer_invoice'],
                    'documentReference'    => ['string', null],
                ],
                1,
                'The conditional ("customerDocumentType != other") required param "documentReference" is not set!',
                [
                    'EavDocumentConditionalNotRequiredConstraint' => [
                        'checkAttribute'    => 'customerDocumentType',
                        'value'             => 'other',
                        'requiredAttribute' => 'documentReference',
                    ],
                ],
            ],
            'conditional not required attribute empty' => [
                [
                    'customerDocumentType' => ['required' => true],
                    'documentReference'    => ['required' => false],
                ],
                [
                    'customerDocumentType' => ['string', 'customer_invoice'],
                    'documentReference'    => ['string', ''],
                ],
                1,
                'The conditional ("customerDocumentType != other") required param "documentReference" is not set!',
                [
                    'EavDocumentConditionalNotRequiredConstraint' => [
                        'checkAttribute'    => 'customerDocumentType',
                        'value'             => 'other',
                        'requiredAttribute' => 'documentReference',
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests the validation of EavDocument entity
     *
     * @param array  $typeAttributes
     * @param array  $valueAttributes
     * @param int    $violationsCount
     * @param string $violationMessage
     * @param array  $typeValidators
     *
     * @dataProvider validateDataProvider
     */
    public function testValidateEavDocument(
        $typeAttributes,
        $valueAttributes,
        $violationsCount,
        $violationMessage,
        $typeValidators = []
    ) {
        $eavDocument = $this->getEavDocumentEntity($typeAttributes, $valueAttributes, $typeValidators);

        $this->validateEntity($eavDocument, $violationsCount, $violationMessage);
    }

    /**
     * Returns new EavDocument entity with attributes and values
     *
     * @param array $typeAttributes
     * @param array $values
     * @param array $typeValidators
     *
     * @return EavDocument
     */
    protected function getEavDocumentEntity($typeAttributes, $values = [], $typeValidators = [])
    {
        $eavType = new EavType();
        $eavType
            ->setName('new type')
            ->setAlias('new_type')
            ->setAttributes($typeAttributes)
            ->setValidators($typeValidators);

        $eavDocument = new EavDocument();
        $eavDocument->setType($eavType);

        if (!empty($values)) {
            $i = 0;

            foreach ($values as $attribute => $attributeData) {
                ++$i;
                list($type, $value) = $attributeData;

                $eavAttr = new EavAttribute();
                $eavAttr
                    ->setType($type)
                    ->setName($attribute)
                    ->setValidators([]);

                $eavValue = $eavDocument->createNewValue($value, $eavAttr);

                $eavDocument->setRawEavValue($eavValue);
            }
        }

        return $eavDocument;
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testTypeNotSetException()
    {
        $sut = new EavDocumentConstraintValidator();

        $sut->validate(new EavDocument(), new EavDocumentConstraint());
    }
}
