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

namespace SebastianBergmann\PHPCPD\Log;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\PHPCPD\CodeClone;
use SebastianBergmann\PHPCPD\CodeCloneFile;
use SebastianBergmann\PHPCPD\CodeCloneMap;

#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Log\AbstractXmlLogger::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\SebastianBergmann\PHPCPD\Log\PMD::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeClone::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneFile::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneMap::class)]
#[\PHPUnit\Framework\Attributes\UsesClass('\\'.\SebastianBergmann\PHPCPD\CodeCloneMapIterator::class)]
final class PMDTest extends TestCase
{
    private string $testFile1;

    private string $testFile2;

    private string|false $pmdLogFile;

    private string|false $expectedPmdLogFile;

    private PMD $pmdLogger;

    protected function setUp(): void
    {
        $this->testFile1 = __DIR__.'/../fixture/with_ascii_escape.php';
        $this->testFile2 = __DIR__.'/../fixture/with_ascii_escape2.php';

        $this->pmdLogFile = tempnam(sys_get_temp_dir(), 'pmd');

        $this->expectedPmdLogFile = tempnam(sys_get_temp_dir(), 'pmd');
        $expectedPmdLogTemplate = __DIR__.'/../fixture/pmd_expected.xml';

        $expectedPmdLogContents = strtr(
            file_get_contents($expectedPmdLogTemplate),
            [
                '%file1%' => $this->testFile1,
                '%file2%' => $this->testFile2,
            ]
        );

        file_put_contents($this->expectedPmdLogFile, $expectedPmdLogContents);

        $this->pmdLogger = new PMD($this->pmdLogFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->pmdLogFile)) {
            unlink($this->pmdLogFile);
        }

        if (file_exists($this->expectedPmdLogFile)) {
            unlink($this->expectedPmdLogFile);
        }
    }

    public function testSubstitutesDisallowedCharacters(): void
    {
        $file1 = new CodeCloneFile($this->testFile1, 8);
        $file2 = new CodeCloneFile($this->testFile2, 8);
        $clone = new CodeClone($file1, $file2, 4, 4);
        $cloneMap = new CodeCloneMap();

        $cloneMap->add($clone);

        $this->pmdLogger->processClones($cloneMap);

        $this->assertXmlFileEqualsXmlFile(
            $this->expectedPmdLogFile,
            $this->pmdLogFile
        );
    }
}
