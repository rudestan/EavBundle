<?php

namespace EavBundle\Tests\Validator\Constraints;

use EavBundle\Entity\EavDocument;
use EavBundle\Entity\EavValue;
use EavBundle\Tests\EavKernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class EavAbstractConstraintValidatorTest
 */
abstract class EavAbstractConstraintValidatorTest extends EavKernelTestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Sets up a test
     */
    protected function setUp()
    {
        $this->validator = static::$container->get('validator');
    }

    /**
     * Performs common assertions on the EavEntity (EavDocument, EavValue)
     *
     * @param EavDocument|EavValue $entity
     * @param int                  $violationsCount
     * @param string               $violationMessage
     */
    protected function validateEntity($entity, $violationsCount, $violationMessage)
    {
        $violations = $this->validator->validate($entity);

        $this->assertInstanceOf(ConstraintViolationList::class, $violations);
        $this->assertCount($violationsCount, $violations);

        if ($violationMessage) {
            $violation = $violations->get(0);

            $this->assertInstanceOf(ConstraintViolation::class, $violation);
            $this->assertObjectHasAttribute('message', $violation);
            $this->assertContains($violationMessage, (string) $violation);
        }
    }
}
