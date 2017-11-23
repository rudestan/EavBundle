<?php

namespace EavBundle\Tests\Validator\Constraints;

use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavValue;
use EavBundle\Validator\Constraints\EavValueConstraint;
use EavBundle\Validator\Constraints\EavValueConstraintValidator;

/**
 * Class EavValueConstraintValidatorTest
 */
class EavValueConstraintValidatorTest extends EavAbstractConstraintValidatorTest
{
    /**
     * Data provider for EavValue validation test
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'validate type null value' => [
                'new_value',
                null,
                'string',
                [],
                0,
                null,
            ],
            'validators not an array' => [
                'new_value',
                '123',
                'string',
                [],
                0,
                null,
            ],
            'constraint class not found' => [
                'new_value',
                '123',
                'string',
                [
                    'NonExistingClass' => [],
                ],
                1,
                'Object(EavBundle\Entity\EavValue).NonExistingClass',
            ],
            'constraint not blank' => [
                'new_blank_value',
                '',
                'string',
                [
                    'NotBlank' => [],
                ],
                1,
                'Object(EavBundle\Entity\EavValue).new_blank_value',
            ],
            'constraint range' => [
                'new_range_value',
                1,
                'integer',
                [
                    'Range' => ['min' => 2],
                ],
                1,
                'Object(EavBundle\Entity\EavValue).new_range_value',
            ],
            'constraint less than' => [
                'new_less_than_value',
                1000,
                'integer',
                [
                    'LessThan' => ['value' => 999],
                ],
                1,
                'Object(EavBundle\Entity\EavValue).new_less_than_value',
            ],
            'constraint length' => [
                'new_length_value',
                'some long text',
                'string',
                [
                    'Length' => ['max' => 3],
                ],
                1,
                'Object(EavBundle\Entity\EavValue).new_length_value',
            ],
            'valid value 1' => [
                'new_valid_value_1',
                'some text',
                'string',
                [
                    'NotBlank' => [],
                    'Length'   => ['min' => 4, 'max' => 10],
                ],
                0,
                null,
            ],
            'valid value 2' => [
                'new_valid_value_2',
                193456,
                'integer',
                [
                    'NotBlank' => [],
                    'Range'   => ['min' => 193450, 'max' => 193457],
                ],
                0,
                null,
            ],
        ];
    }

    /**
     * Tests the validation of EavValue entity
     *
     * @param string     $name
     * @param mixed      $value
     * @param string     $type
     * @param null|array $validators
     * @param int        $violationCount
     * @param string     $violationMessage
     *
     * @dataProvider validateDataProvider
     */
    public function testValidateEavValue($name, $value, $type, $validators, $violationCount, $violationMessage)
    {
        $eavValue = $this->getEavValueEntity($name, $value, $type, $validators);

        $this->validateEntity($eavValue, $violationCount, $violationMessage);
    }

    /**
     * Returns new EavValue entity
     *
     * @param string     $name
     * @param mixed      $value
     * @param string     $type
     * @param null|array $validators
     *
     * @return EavValue
     */
    protected function getEavValueEntity($name, $value, $type, $validators = null)
    {
        $eavAttribute = new EavAttribute();
        $eavAttribute
            ->setType($type)
            ->setName($name)
            ->setValidators($validators);

        $eavValue = new EavValue();
        $eavValue
            ->setValue($value)
            ->setAttribute($eavAttribute);

        return $eavValue;
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testAttributeNotSetException()
    {
        $sut = new EavValueConstraintValidator();

        $sut->validate(new EavValue(), new EavValueConstraint());
    }
}
