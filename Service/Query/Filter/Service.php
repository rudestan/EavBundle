<?php

namespace EavBundle\Service\Query\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use EavBundle\Entity\EavAttribute;
use Nbb\Oms\ApiBundle\Exception\ApiException;
use Nbb\Oms\ApiBundle\Service\Query\Filter\Service as BaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Service
 */
class Service extends BaseService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var int
     */
    protected $documentTypeId;

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $documentTypeId
     */
    public function setDocumentTypeId($documentTypeId)
    {
        $this->documentTypeId = $documentTypeId;
    }

    /**
     * Applies parsed and created instances of filters on the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @param Request      $request
     */
    public function filter(QueryBuilder $queryBuilder, Request $request)
    {
        if ($this->documentTypeId === null) {
            throw new ApiException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Document type id must be set for filtering'
            );
        }

        $parsedFilters       = $this->parseFilters($request);
        $preProcessedFilters = $this->preProcessFilters($parsedFilters);
        $this->makeFilters($preProcessedFilters);

        foreach ($this->filters as $filter) {
            $filter->filter($queryBuilder);
        }
    }

    protected function preProcessFilters($parsedFilters)
    {
        foreach ($parsedFilters as $key => $filter) {
            $attributeName = $this->entityManager->getRepository('EavBundle:EavType')
                ->getAttributeRealNameByAlias($this->documentTypeId, $filter['field']);

            /** @var EavAttribute $attribute */
            $attribute = $this->entityManager->getRepository('EavBundle:EavAttribute')
                ->findOneBy(['name' => $attributeName]);

            $parsedFilters[$key]['field']       = $attributeName;
            $parsedFilters[$key]['attributeId'] = $attribute->getId();
        }

        return $parsedFilters;
    }
}
