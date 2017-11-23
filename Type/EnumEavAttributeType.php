<?php

namespace EavBundle\Type;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Class EnumEavAttributeType
 */
class EnumEavAttributeType extends Type
{
    const TYPE_INTEGER = 'integer';

    const TYPE_STRING = 'string';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_DOUBLE = 'double';

    const TYPE_ARRAY = 'array';

    protected $name = 'enumEavAttributeType';

    protected $values = [
        self::TYPE_INTEGER,
        self::TYPE_STRING,
        self::TYPE_BOOLEAN,
        self::TYPE_DOUBLE,
        self::TYPE_ARRAY,
    ];

    /**
     * Gets the SQL declaration snippet for a field of this type
     *
     * @param array            $fieldDeclaration The field declaration
     * @param AbstractPlatform $platform         The currently used database platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return 'ENUM(' . implode(', ', $values) . ')';
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->values)) {
            throw new InvalidArgumentException("Invalid '" . $this->name . "' value.");
        }

        return $value;
    }

    /**
     * Gets the name of this type
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param AbstractPlatform $platform
     *
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
