<?php

namespace EavBundle\Entity;

use EavBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class EavValue
 *
 * @ORM\Table(
 *      name="eav_value",
 *      indexes={
 *          @ORM\Index(name="idx_eav_value", columns={"value"}),
 *          @ORM\Index(name="idx_eav_value_eav_attribute_id", columns={"eav_attribute_id"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="idx_eav_value_document_attribute_unique", columns={"eav_document_id","eav_attribute_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="EavBundle\Repository\EavValueRepository")
 *
 * @AppAssert\EavValueTypeConstraint
 * @AppAssert\EavValueConstraint
 */
class EavValue
{
    /**
     * @var EavDocument
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EavDocument", inversedBy="values")
     * @ORM\JoinColumn(name="eav_document_id", referencedColumnName="id")
     */
    protected $document;

    /**
     * @var EavAttribute|EavPromisedAttribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EavAttribute")
     * @ORM\JoinColumn(name="eav_attribute_id", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var mixed
     *
     * @ORM\Column(name="value", type="string", length=256, nullable=true)
     */
    protected $value;

    /**
     * @return EavDocument|null
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param EavDocument $document
     *
     * @return EavValue
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return EavAttribute|EavPromisedAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param EavAttribute|EavPromisedAttribute $attribute
     *
     * @return EavValue
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return EavValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
