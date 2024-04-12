<?php

declare(strict_types=1);

/*
 * This file is part of PHP Copy/Paste Detector (PHPCPD).
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\PHPCPD\Detector;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\PHPCPD\ArgumentsBuilder;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPCPD\Detector\Strategy\StrategyConfiguration;
use SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTreeStrategy;
use Symfony\Component\Finder\Finder;

#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Arguments::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\ArgumentsBuilder::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Detector::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\AbstractStrategy::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\StrategyConfiguration::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\ApproximateCloneDetectingSuffixTree::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\CloneInfo::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\PairList::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\Sentinel::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\SuffixTree::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\SuffixTreeHashTable::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTree\Token::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTreeStrategy::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeClone::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneFile::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneMap::class)]
final class EditDistanceTest extends TestCase
{
    public function testEditDistanceWithSuffixtree(): void
    {
        $argv = [1 => '.', '--min-tokens', '60'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy = new SuffixTreeStrategy($config);

        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('editdistance[1|2].php')
        );

        $clones = $clones->clones();
        $this->assertCount(1, $clones);
    }

    public function testEditDistanceWithRabinkarp(): void
    {
        $argv = [1 => '.', '--min-tokens', '60'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy = new DefaultStrategy($config);

        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('editdistance[1|2].php')
        );

        $clones = $clones->clones();
        $this->assertCount(0, $clones);
    }
}
