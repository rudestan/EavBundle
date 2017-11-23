<?php

namespace EavBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Nbb\Oms\ApiBundle\Repository\BaseRepository;

/**
 * EavDocumentRepository
 */
class EavDocumentRepository extends BaseRepository
{
    /**
     * Returns documents searched by type and criteria
     *
     * @param int   $typeId
     * @param array $criteria
     * @param array $excludeIds
     *
     * @return array
     */
    public function findByTypeIdAndCriteria($typeId, array $criteria, array $excludeIds = [])
    {
        /** @var EavAttributeRepository $eavAttributeRepo */
        $eavAttributeRepo = $this->getEntityManager()->getRepository('EavBundle:EavAttribute');
        $attributeIds     = $eavAttributeRepo->getAttributeIds(array_keys($criteria));
        $queryBuilder     = $this->getEavQueryBuilder($typeId);

        $this->addAndCriteria($queryBuilder, $criteria, $attributeIds);

        if (!empty($excludeIds)) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('d.id', ':exclude_ids'));
            $queryBuilder->setParameter('exclude_ids', $excludeIds);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $typeId
     *
     * @return QueryBuilder
     */
    public function getEavQueryBuilder($typeId)
    {
        $queryBuilder = $this->createQueryBuilder('d')->where('d.type = :type_id');
        $queryBuilder->setParameter('type_id', $typeId);

        return $queryBuilder;
    }

    /**
     * Adds And criteria to the query
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     * @param array        $attributeIds
     */
    protected function addAndCriteria(QueryBuilder $queryBuilder, array $criteria, array $attributeIds)
    {
        $i = 0;

        foreach ($criteria as $field => $value) {
            ++$i;

            $queryBuilder->innerJoin('d.values', 'v' . $i);

            $queryBuilder
                ->andWhere(sprintf('v%1$s.attribute = :va%1$s_%2$s', $i, $field))
                ->andWhere(sprintf('v%1$s.value = :vv%1$s_%2$s', $i, $field));

            $queryBuilder
                ->setParameter('va' . $i . '_' . $field, $attributeIds[$field])
                ->setParameter('vv' . $i . '_' . $field, $value);
        }
    }
}
