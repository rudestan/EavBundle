<?php

namespace EavBundle\Entity;

use EavBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as JMSerializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class EavDocument
 *
 * @ORM\Table(
 *      name="eav_document",
 *      indexes={
 *          @ORM\Index(name="idx_eav_document_eav_type_id", columns={"eav_type_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="EavBundle\Repository\EavDocumentRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMSerializer\ExclusionPolicy("none")
 * @JMSerializer\AccessorOrder("custom", custom={"id", "getValuesForSerialization"})
 *
 * @AppAssert\EavUnsupportedAttributesConstraint
 * @AppAssert\EavRequiredAttributesConstraint
 * @AppAssert\EavDocumentConstraint
 */
class EavDocument
{
    use EavDocumentValuesTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var EavType
     *
     * @ORM\ManyToOne(targetEntity="EavType")
     * @ORM\JoinColumn(name="eav_type_id", referencedColumnName="id")
     *
     * @JMSerializer\Exclude
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=120, nullable=true)
     *
     * @JMSerializer\Exclude
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     *
     * @JMSerializer\Exclude
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="modified_at", type="datetime", nullable=true)
     *
     * @JMSerializer\Exclude
     */
    protected $modifiedAt;

    /**
     * @var ArrayCollection|PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="EavValue", mappedBy="document", cascade={"persist", "remove", "refresh"},
     *                                         orphanRemoval=true)
     * @ORM\JoinColumn(name="eav_value", referencedColumnName="eav_document_id")
     *
     * @JMSerializer\Exclude
     */
    protected $values;

    /**
     * @var ConstraintViolationList
     *
     * @JMSerializer\Exclude
     */
    protected $lastViolations;

    /**
     * EavDocument constructor
     */
    public function __construct()
    {
        $this->values         = new ArrayCollection();
        $this->lastViolations = new ConstraintViolationList();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return EavType|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param EavType $type
     *
     * @return EavDocument
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return EavDocument
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @return ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param ArrayCollection|PersistentCollection $values
     *
     * @return EavDocument
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return ConstraintViolationList
     */
    public function getLastViolations()
    {
        return $this->lastViolations;
    }

    /**
     * @param ConstraintViolationListInterface $lastViolations
     *
     * @return EavDocument
     */
    public function setLastViolations($lastViolations)
    {
        $this->lastViolations = $lastViolations;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->modifiedAt = new \DateTime();
    }

    /**
     * Returns an array of the attribute names and corresponding values
     *
     * @JMSerializer\VirtualProperty
     * @JMSerializer\SerializedName("values")
     * @JMSerializer\Inline
     *
     * @return array
     */
    public function getValuesForSerialization()
    {
        $values = [];

        if (!$this->values->count()) {
            return $values;
        }

        $type = $this->getType();

        if (!$type instanceof EavType) {
            return $values;
        }

        $attributes = $type->getAttributes();

        if (empty($attributes)) {
            return $values;
        }

        foreach ($attributes as $name => $options) {
            $key   = isset($options['alias']) ? $options['alias'] : $name;
            $value = $this->getValue($name);

            if (!$value instanceof EavValue) {
                continue;
            }

            $values[$key] = $value->getValue();
        }

        return $values;
    }
}
