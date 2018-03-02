<?php namespace Decahedron\Vulcan\Definitions;

/**
 * This is for when you want to define an ES type and some mappings, but without going whole-hog with auto-indexing
 * and Eloquent stuff, like in a SearchDefinition.
 */
interface HasStandaloneMappings extends HasMappings
{
    public static function getType(): string;
}