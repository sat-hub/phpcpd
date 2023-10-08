<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests/unit',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/tests/fixture',
    ]);

    // register a single rule

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        //SetList::NAMING,
        //SetList::TYPE_DECLARATION,
        //SetList::PRIVATIZATION,
        //SetList::EARLY_RETURN,
        //SetList::TYPE_DECLARATION,
        //SetList::INSTANCEOF,
        //PHPUnitSetList::PHPUNIT_100
        //LevelSetList::UP_TO_PHP_81
    ]);
};
