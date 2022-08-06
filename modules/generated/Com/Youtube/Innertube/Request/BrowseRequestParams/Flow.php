<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: innertube/request/browse_request_params.proto

namespace Com\Youtube\Innertube\Request\BrowseRequestParams;

use UnexpectedValueException;

/**
 *optional View view = 4;
 *
 * Protobuf type <code>com.youtube.innertube.request.BrowseRequestParams.Flow</code>
 */
class Flow
{
    /**
     * Generated from protobuf enum <code>DEFAULT_FLOW = 0;</code>
     */
    const DEFAULT_FLOW = 0;
    /**
     * Generated from protobuf enum <code>GRID = 1;</code>
     */
    const GRID = 1;
    /**
     * Generated from protobuf enum <code>LIST = 2;</code>
     */
    const PBLIST = 2;

    private static $valueToName = [
        self::DEFAULT_FLOW => 'DEFAULT_FLOW',
        self::GRID => 'GRID',
        self::PBLIST => 'PBLIST',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Flow::class, \Com\Youtube\Innertube\Request\BrowseRequestParams_Flow::class);

