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
use SebastianBergmann\PHPCPD\Detector\Strategy\AbstractStrategy;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPCPD\Detector\Strategy\StrategyConfiguration;
use Symfony\Component\Finder\Finder;

#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Arguments::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\ArgumentsBuilder::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Detector::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\AbstractStrategy::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Detector\Strategy\StrategyConfiguration::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeClone::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneFile::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneMap::class)]
final class DetectorTest extends TestCase
{
    /**
     * @psalm-param AbstractStrategy $strategy
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testDetectingSimpleClonesWorks(AbstractStrategy $strategy): void
    {
        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('Math.php')
        );

        $clones = $clones->clones();

        $files = $clones[0]->files();
        $file = current($files);

        $this->assertSame(realpath(__DIR__.'/../fixture/Math.php'), $file->name());
        $this->assertSame(75, $file->startLine());

        $file = next($files);

        $this->assertSame(realpath(__DIR__.'/../fixture/Math.php'), $file->name());
        $this->assertSame(139, $file->startLine());
        $this->assertSame(59, $clones[0]->numberOfLines());
        $this->assertSame(136, $clones[0]->numberOfTokens());

        $this->assertSame(
            '    public function div($v1, $v2)
    {
        $v3 = $v1 / ($v2 + $v1);
        if ($v3 > 14)
        {
            $v4 = 0;
            for ($i = 0; $i < $v3; $i++)
            {
                $v4 += ($v2 * $i);
            }
        }
        $v5 = ($v4 < $v3 ? ($v3 - $v4) : ($v4 - $v3));

        $v6 = ($v1 * $v2 * $v3 * $v4 * $v5);

        $d = array($v1, $v2, $v3, $v4, $v5, $v6);

        $v7 = 1;
        for ($i = 0; $i < $v6; $i++)
        {
            shuffle( $d );
            $v7 = $v7 + $i * end($d);
        }

        $v8 = $v7;
        foreach ( $d as $x )
        {
            $v8 *= $x;
        }

        $v3 = $v1 / ($v2 + $v1);
        if ($v3 > 14)
        {
            $v4 = 0;
            for ($i = 0; $i < $v3; $i++)
            {
                $v4 += ($v2 * $i);
            }
        }
        $v5 = ($v4 < $v3 ? ($v3 - $v4) : ($v4 - $v3));

        $v6 = ($v1 * $v2 * $v3 * $v4 * $v5);

        $d = array($v1, $v2, $v3, $v4, $v5, $v6);

        $v7 = 1;
        for ($i = 0; $i < $v6; $i++)
        {
            shuffle( $d );
            $v7 = $v7 + $i * end($d);
        }

        $v8 = $v7;
        foreach ( $d as $x )
        {
            $v8 *= $x;
        }

        return $v8;
',
            $clones[0]->lines()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testDetectingExactDuplicateFilesWorks(AbstractStrategy $strategy): void
    {
        $argv = [1 => '.', '--min-lines', '20', '--min-tokens', '50'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);

        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(\dirname(__DIR__).'/fixture')->name('[a|b].php')
        );

        $clones = $clones->clones();

        $files = $clones[0]->files();
        ksort($files);
        $file = current($files);
        $this->assertCount(1, $clones);
        $this->assertSame(\dirname(__DIR__).'/fixture/a.php', $file->name());
        $this->assertSame(4, $file->startLine());

        $file = next($files);

        $this->assertSame(\dirname(__DIR__).'/fixture/b.php', $file->name());
        $this->assertSame(4, $file->startLine());
        $this->assertSame(20, $clones[0]->numberOfLines());
        $this->assertSame(60, $clones[0]->numberOfTokens());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testDetectingClonesInMoreThanTwoFiles(AbstractStrategy $strategy): void
    {
        $argv = [1 => '.', '--min-lines', '20', '--min-tokens', '60'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);

        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(\dirname(__DIR__).'/fixture')->name('[a|b|c].php')
        );

        $clones = $clones->clones();
        // var_dump($clones);
        $files = $clones[0]->files();
        sort($files);

        $file = current($files);

        $this->assertCount(1, $clones);
        $this->assertSame(\dirname(__DIR__).'/fixture/a.php', $file->name());
        $this->assertSame(4, $file->startLine());

        $file = next($files);

        $this->assertSame(\dirname(__DIR__).'/fixture/b.php', $file->name());
        $this->assertSame(4, $file->startLine());

        $file = next($files);

        $this->assertSame(\dirname(__DIR__).'/fixture/c.php', $file->name());
        $this->assertSame(4, $file->startLine());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testClonesAreIgnoredIfTheySpanLessTokensThanMinTokens(AbstractStrategy $strategy): void
    {
        $argv = [1 => '.', '--min-lines', '20', '--min-tokens', '61'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);
        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(\dirname(__DIR__).'/fixture')->name('[a|b].php')
        );

        $this->assertCount(0, $clones->clones());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testClonesAreIgnoredIfTheySpanLessLinesThanMinLines(AbstractStrategy $strategy): void
    {
        $argv = [1 => '.', '--min-lines', '21', '--min-tokens', '60'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);
        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('[a|b].php')
        );

        $this->assertCount(0, $clones->clones());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testFuzzyClonesAreFound(AbstractStrategy $strategy): void
    {
        $argv = [1 => '.', '--min-lines', '5', '--min-tokens', '20', '--fuzzy', 'true'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);
        $clones = (new Detector($strategy))->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('[a|b].php')
        );

        $this->assertCount(1, $clones->clones());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('strategyProvider')]
    public function testStripComments(AbstractStrategy $strategy): void
    {
        $argv = [1 => '.', '--min-lines', '8', '--min-tokens', '10', '--fuzzy', 'true'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);

        $detector = new Detector($strategy);

        $clones = $detector->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('[e|f].php')
        );

        $this->assertCount(0, $clones->clones());

        $argv = [1 => '.', '--min-lines', '7', '--min-tokens', '10', '--fuzzy', 'true'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);
        $strategy->setConfig($config);

        $clones = $detector->copyPasteDetection(
            (new Finder())->in(__DIR__.'/../fixture')->name('[e|f].php')
        );

        $this->assertCount(1, $clones->clones());
    }

    /**
     * @psalm-return list<AbstractStrategy>
     */
    public static function strategyProvider(): array
    {
        // Build default config.
        $argv = [1 => '.'];
        $arguments = (new ArgumentsBuilder())->build($argv);
        $config = new StrategyConfiguration($arguments);

        return [
            [new DefaultStrategy($config)],
        ];
    }
}
