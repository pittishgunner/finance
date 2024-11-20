<?php

namespace App\Constant;

use ReflectionClass;
use ValueError;

/**
 * This is kind of like an "enum" but it's more lenient.
 * We don't have to use "->value" to get the actual value.
 * Which is great when using the value to set a property's default value.
 * Plus we can extend its children with any other data or functions they need.
 */
abstract class BaseConstant
{
    /**
     * @var array<int|string,string>
     */
    protected static array $labels = [];

    /**
     * @param null|int|string $value
     * @return null|int|string Null if the value is not valid, or the value if it is.
     */
    protected static function getValidValue($value)
    {
        if (null === $value) {
            return null;
        }

        if (in_array($value, static::cases())) {
            return $value;
        }

        return null;
    }

    /**
     * @return array<string,int|string>
     */
    public static function cases(): array
    {
        // array_filter is used because getConstants returns array<string,mixed>
        // and BaseConstant only works with int|string constants
        return array_filter(
            (new ReflectionClass(static::class))->getConstants(),
            fn($constant) => is_string($constant) || is_int($constant)
        );
    }

    /**
     * Useful for listing available cases in comments.
     * Example output for string cases: case1, case2, case3
     * Example output for other type cases: case1 - label1, case2 - label2, case3 - label3
     */
    public static function getLabelledCases(string $separator = ', '): string
    {
        $cases = static::cases();
        $casesString = [];
        foreach ($cases as $case) {
            if (is_string($case)) {
                $casesString[] = $case;
            } else {
                $casesString[] = sprintf('%s - %s', (string) $case, static::tryGetLabel($case));
            }
        }

        return implode($separator, $casesString);
    }

    /**
     * @param null|int|string $value
     * @return int|string
     * @throws ValueError
     */
    public static function from($value)
    {
        $foundValue = static::getValidValue($value);

        if (null === $foundValue) {
            throw new ValueError(sprintf('Invalid value `%s` for %s', (string) $value, static::class));
        }

        return $foundValue;
    }

    /**
     * @param null|int|string $value
     * @return null|int|string
     */
    public static function tryFrom($value)
    {
        return static::getValidValue($value);
    }

    /**
     * @return array<int|string,string>
     */
    protected static function getGeneratedLabels(): array
    {
        if (count(static::cases()) > 0) {
            return array_map(
                fn($label) => ucfirst(str_replace('_', ' ', strtolower($label))),
                array_flip(static::cases())
            );
        }

        return [];
    }

    /**
     * @return array<int|string,string>
     */
    public static function getLabels(): array
    {
        return (count(static::$labels) > 0 ? static::$labels : static::getGeneratedLabels());
    }

    /**
     * @param null|int|string $value
     * @throws ValueError
     */
    public static function getLabel($value): string
    {

        return static::getLabels()[static::from($value)];
    }

    /**
     * @param null|int|string $value
     */
    public static function tryGetLabel($value): string
    {
        $value = static::tryFrom($value);

        return ($value !== null ? static::getLabels()[$value] : '');
    }
}
