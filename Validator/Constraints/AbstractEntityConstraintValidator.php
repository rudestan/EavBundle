<?php

namespace EavBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class AbstractEntityConstraintValidator
 */
abstract class AbstractEntityConstraintValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    protected $namespaces = [
        'EavBundle\Validator\Constraints\%s',
        'Symfony\Component\Validator\Constraints\%s',
    ];

    /**
     * @var object
     */
    protected $entity;

    /**
     * Validates the entity
     *
     * @param object     $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $this->entity = $entity;
        $validators   = $this->getValidators();

        if (!is_array($validators) || empty($validators)) {
            return;
        }

        $constraints = $this->buildConstraints($validators, $constraint);
        $violations  = $this->context->getValidator()->validate($this->getValueToValidate(), $constraints);

        $this->addViolationsToCurrentContext($violations);
    }

    /**
     * Returns validators array
     *
     * @return array
     */
    abstract protected function getValidators();

    /**
     * Returns path
     *
     * @return string
     */
    abstract protected function getPath();

    /**
     * Returns value to validate
     *
     * @return mixed
     */
    abstract protected function getValueToValidate();

    /**
     * Builds constraints from assoc array. Returns array of constraint classes
     *
     * @param Constraint $constraint
     * @param array      $constraintsList
     *
     * @return array
     */
    protected function buildConstraints(array $constraintsList, Constraint $constraint)
    {
        $constraints = [];

        foreach ($constraintsList as $name => $options) {
            $namespaces   = $this->namespaces;
            $namespaces[] = $name;

            $class = $this->getConstraintClass($namespaces, $name);

            if ($class === null) {
                $this->context->setConstraint($constraint);
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ class }}', $name)
                    ->atPath($name)
                    ->addViolation();

                continue;
            }

            $constraints[] = new $class($options);
        }

        return $constraints;
    }

    /**
     * Returns constraint class if found otherwise returns null
     *
     * @param array  $namespaces
     * @param string $name
     *
     * @return null|string
     */
    protected function getConstraintClass(array $namespaces, $name)
    {
        foreach ($namespaces as $namespace) {
            $class = sprintf($namespace, $name);

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Adds all passed violations to the current context
     *
     * @param ConstraintViolationListInterface $violations
     */
    protected function addViolationsToCurrentContext(ConstraintViolationListInterface $violations)
    {
        foreach ($violations as $violation) {
            $this->context->setConstraint($violation->getConstraint());
            $this->context
                ->buildViolation($violation->getMessage(), $violation->getParameters())
                ->atPath($violation->getPropertyPath() ? $violation->getPropertyPath() : $this->getPath())
                ->addViolation();
        }
    }
}
