<?php namespace Decahedron\Vulcan\Definitions;

interface HasMappings
{
    public static function getFieldMapping(): array;
}