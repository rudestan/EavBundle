<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Types\Type;
use EavBundle\Type\EnumEavAttributeType;

$loader = require __DIR__.'/../vendor/autoload.php';

define('KERNEL_DIR', __DIR__ . '../app');

AnnotationRegistry::registerLoader('class_exists');
Type::addType('enumEavAttributeType', EnumEavAttributeType::class);