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

namespace SebastianBergmann\PHPCPD;

use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\AbstractStrategy;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPCPD\Detector\Strategy\StrategyConfiguration;
use SebastianBergmann\PHPCPD\Detector\Strategy\SuffixTreeStrategy;
use SebastianBergmann\PHPCPD\Log\Github;
use SebastianBergmann\PHPCPD\Log\PMD;
use SebastianBergmann\PHPCPD\Log\Text;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;
use SebastianBergmann\Version;
use Symfony\Component\Finder\Finder;

final class Application
{
    private const VERSION = '7.0.1';

    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        $this->printVersion();

        try {
            $arguments = (new ArgumentsBuilder())->build($argv);
        } catch (Exception $exception) {
            echo \PHP_EOL.$exception->getMessage().\PHP_EOL;

            return 1;
        }

        if ($arguments->version()) {
            return 0;
        }

        echo \PHP_EOL;

        if ($arguments->help()) {
            $this->help();

            return 0;
        }

        $files = (new Finder())->in($arguments->directories())
            ->notPath($arguments->exclude())
            ->files()
            ->name($arguments->suffixes());

        if (0 == iterator_count($files)) {
            echo 'No files found to scan'.\PHP_EOL;

            return 1;
        }

        $config = new StrategyConfiguration($arguments);

        try {
            $strategy = $this->pickStrategy($arguments->algorithm(), $config);
        } catch (InvalidStrategyException $invalidStrategyException) {
            echo $invalidStrategyException->getMessage().\PHP_EOL;

            return 1;
        }

        $timer = new Timer();
        $timer->start();

        $clones = (new Detector($strategy))->copyPasteDetection($files);

        (new Text())->printResult($clones, $arguments->verbose());

        if ($arguments->pmdCpdXmlLogfile()) {
            (new PMD($arguments->pmdCpdXmlLogfile()))->processClones($clones);
        }

        if ($arguments->githubLogOutput()) {
            (new Github())->processClones($clones);
        }

        echo (new ResourceUsageFormatter())->resourceUsage($timer->stop()).\PHP_EOL;

        return \count($clones) > 0 ? 1 : 0;
    }

    private function printVersion(): void
    {
        printf(
            'phpcpd %s by Sebastian Bergmann, Matthias Krauser, Sascha Ternes.'.\PHP_EOL,
            (new Version(self::VERSION, \dirname(__DIR__)))->asString()
        );
    }

    /**
     * @throws InvalidStrategyException
     */
    private function pickStrategy(?string $algorithm, StrategyConfiguration $config): AbstractStrategy
    {
        return match ($algorithm) {
            null, 'rabin-karp' => new DefaultStrategy($config),
            'suffixtree' => new SuffixTreeStrategy($config),
            default => throw new InvalidStrategyException('Unsupported algorithm: '.$algorithm),
        };
    }

    private function help(): void
    {
        echo <<<'EOT'
Usage:
  phpcpd [options] <directories>

Options for selecting files:

  --suffix <suffix> Include files with names ending in <suffix> in the analysis
                    (default: *.php; can be given multiple times)
  --exclude <path>  Exclude files with <path> in their path from the analysis
                    The patterns given need to be relative to the analyzed paths
                    (can be given multiple times)

Options for analysing files:

  --fuzzy             Fuzz variable names
  --min-lines <N>     Minimum number of identical lines (default: 5)
  --min-tokens <N>    Minimum number of identical tokens (default: 70)
  --algorithm <name>  Select which algorithm to use ('rabin-karp' (default) or 'suffixtree')
  --edit-distance <N> Distance in number of edits between two clones (only for suffixtree; default: 5)
  --head-equality <N> Minimum equality at start of clone (only for suffixtree; default 10)

Options for report generation:

  --log-pmd <file>  Write log in PMD-CPD XML format to <file>
  --log-github      Write log to stdout formatted to create github pr annotations

EOT;
    }
}
