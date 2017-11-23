<?php

declare(strict_types=1);

namespace EavBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * EavTypeRepository
 */
class EavTypeRepository extends EntityRepository
{
    /**
     * @param int    $documentTypeId
     * @param string $attributeAlias
     *
     * @return string
     */
    public function getAttributeRealNameByAlias(int $documentTypeId, string $attributeAlias): string
    {
        return $this->getAttributeRealNameBasedOnProperty(
            $documentTypeId,
            'alias',
            $attributeAlias,
            $attributeAlias
        );
    }

    /**
     * Returns the real attribute name of the "subjectId"
     *
     * @param int $documentTypeId
     *
     * @return null|string
     */
    public function getSubjectIdAttributeRealName(int $documentTypeId): ?string
    {
        return $this->getAttributeRealNameByRole($documentTypeId, 'subjectId');
    }

    /**
     * Returns the real attribute name of the "type"
     *
     * @param int $documentTypeId
     *
     * @return null|string
     */
    public function getTypeAttributeRealName(int $documentTypeId): ?string
    {
        return $this->getAttributeRealNameByRole($documentTypeId, 'type');
    }

    /**
     * Returns the real attribute name of the "documentReference"
     *
     * @param int $documentTypeId
     *
     * @return null|string
     */
    public function getDocumentReferenceAttributeRealName(int $documentTypeId): ?string
    {
        return $this->getAttributeRealNameByRole($documentTypeId, 'documentReference');
    }

    /**
     * @param int    $documentTypeId
     * @param string $attributeRole
     *
     * @return null|string
     */
    protected function getAttributeRealNameByRole(int $documentTypeId, string $attributeRole): ?string
    {
        return $this->getAttributeRealNameBasedOnProperty($documentTypeId, 'role', $attributeRole);
    }

    /**
     * @param int $documentTypeId
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilderWithTypeIdFilter(int $documentTypeId): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->select(['t.attributes'])
            ->where('t.id = :typeId')
            ->setParameter('typeId', $documentTypeId);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $filterName
     * @param string       $filterValue
     *
     * @return QueryBuilder
     */
    protected function applyFilterThroughAttributesProperty(
        QueryBuilder $queryBuilder,
        string $filterName,
        string $filterValue
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('t.attributes LIKE :' . $filterName)
            ->setParameter($filterName, '%"' . $filterName . '":"' . $filterValue . '"%');
    }

    /**
     * @param int        $documentTypeId
     * @param string     $propertyName
     * @param string     $propertyValue
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    protected function getAttributeRealNameBasedOnProperty(
        int $documentTypeId,
        string $propertyName,
        string $propertyValue,
        $defaultValue = null
    ): ?string {
        $query = $this->applyFilterThroughAttributesProperty(
            $this->getQueryBuilderWithTypeIdFilter($documentTypeId),
            $propertyName,
            $propertyValue
        )->getQuery();

        try {
            $result = $query->getSingleScalarResult();
            $result = json_decode($result, true);
            $result = array_filter(
                $result,
                function ($value) use ($propertyName, $propertyValue) {
                    if (isset($value[$propertyName]) && $value[$propertyName] === $propertyValue) {
                        return true;
                    }

                    return false;
                }
            );
            $result = current(array_keys($result));
        } catch (NoResultException $e) {
            $result = $defaultValue;
        }

        return $result;
    }
}
