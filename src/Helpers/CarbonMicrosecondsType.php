<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class CarbonMicrosecondsType extends Type
{
    const TYPENAME = 'datetime_microseconds';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (isset($column['version']) && $column['version']) {
            return 'TIMESTAMP';
        }
        if($platform instanceof PostgreSqlPlatform)
            return 'TIMESTAMP(4) WITHOUT TIME ZONE';
        else
            return 'DATETIME(4)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if($value === null || $value instanceof CarbonInterface)
            return $value;

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        $val = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $value);

        if ( ! $val) {
            $val = Carbon::instance(date_create($value));
        }

        if ( ! $val) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                'Y-m-d H:i:s.u'
            );
        }

        return $val;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s.u');
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', 'DateTime']
        );
    }

    public function getName(): string
    {
        return self::TYPENAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
