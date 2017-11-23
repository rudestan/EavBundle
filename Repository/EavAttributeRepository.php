<?php

namespace EavBundle\Repository;

use Doctrine\ORM\EntityRepository;
use EavBundle\Entity\EavAttribute;

/**
 * EavAttributeRepository
 */
class EavAttributeRepository extends EntityRepository
{
    /**
     * Returns an array with attribute names and corresponding ids
     *
     * @param array $attributeNames
     *
     * @return array
     */
    public function getAttributeIds(array $attributeNames)
    {
        $attributeIds = [];
        $result       = $this->findBy(['name' => $attributeNames]);

        if (count($result)) {
            /** @var EavAttribute $attribute */
            foreach ($result as $attribute) {
                $attributeIds[$attribute->getName()] = $attribute->getId();
            }
        }

        return $attributeIds;
    }
}
