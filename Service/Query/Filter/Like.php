<?php

namespace EavBundle\Service\Query\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Class Like
 */
class Like extends AbstractEavFilter
{
    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function filter(QueryBuilder $queryBuilder)
    {
        $index = ucfirst($this->field);

        $queryBuilder->innerJoin('d.values', 'v' . $index);

        $queryBuilder
            ->andWhere(sprintf('v%1$s.attribute = :va%1$s_%2$s', $index, $this->field))
            ->andWhere(sprintf('v%1$s.value LIKE :vv%1$s_%2$s', $index, $this->field));
        $queryBuilder
            ->setParameter('va' . $index . '_' . $this->field, $this->attributeId)
            ->setParameter('vv' . $index . '_' . $this->field, $this->value);
    }
}
