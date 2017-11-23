<?php

namespace EavBundle\Service;

use Doctrine\ORM\EntityManager;
use EavBundle\Entity\EavAttribute;
use EavBundle\Entity\EavType;

/**
 * Class EavFormFieldsBuilder
 */
class EavFormFieldsBuilder
{
    /**
     * @var bool
     */
    protected $creationMode = false;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * EavFormFieldsBuilder constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Prepares the fields of a specific EavType to be displayed in a form
     *
     * @param string $type
     * @param array  $defaultValues
     *
     * @return array
     */
    public function prepareFormFields($type, array $defaultValues = [])
    {
        $fields              = [];
        $typeRepository      = $this->getRepository(EavType::class);
        $attributeRepository = $this->getRepository(EavAttribute::class);
        /** @var EavType $eavType */
        $eavType = $typeRepository->findOneBy(['alias' => $type]);
        if (!$eavType) {
            throw new \LogicException(sprintf('The EavType "%s" doesn\'t exit.', $type));
        }
        $typeAttributes = $eavType->getAttributes();
        $attributeNames = array_keys($typeAttributes);
        $attributes     = $attributeRepository->findBy(['name' => $attributeNames], ['sortOrder' => 'asc']);
        /** @var EavAttribute $attribute */
        foreach ($attributes as $attribute) {
            $name         = $alias = $attribute->getName();
            $defaultValue = isset($defaultValues[$name]) ? $defaultValues[$name] : '';
            if (array_key_exists('alias', $typeAttributes[$name])) {
                $alias        = $typeAttributes[$name]['alias'];
                $defaultValue = isset($defaultValues[$alias]) ? $defaultValues[$alias] : $defaultValue;
            }

            $validators = $attribute->getValidators();
            $options    = isset($validators['Choice']) ? $validators['Choice']['choices'] : [];
            $field      = [
                'required' => $typeAttributes[$name]['required'],
                'value'    => $defaultValue,
                'options'  => $options,
            ];
            $settings   = $attribute->getSettings();

            if ($settings->hasFormSettings()) {
                if ($settings->getFormSettings()->shouldRender() == false) {
                    continue;
                }

                $disabled          = $settings->getFormSettings()->isDisabled();
                $editable          = $settings->getFormSettings()->isEditable();
                $field['disabled'] = $disabled || (!$editable && !$this->creationMode);
            }

            $fields[$alias] = $field;
        }

        return $fields;
    }

    /**
     * @param bool $creationMode
     *
     * @return EavFormFieldsBuilder
     */
    public function setCreationMode($creationMode)
    {
        $this->creationMode = $creationMode;

        return $this;
    }

    /**
     * Returns the repository of an entity
     *
     * @param string $entityClass
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->entityManager->getRepository($entityClass);
    }
}
