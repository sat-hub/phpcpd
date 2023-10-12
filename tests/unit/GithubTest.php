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

/**
 * @covers \SebastianBergmann\PHPCPD\Log\Github
 *
 * @uses \SebastianBergmann\PHPCPD\CodeClone
 * @uses \SebastianBergmann\PHPCPD\CodeCloneFile
 * @uses \SebastianBergmann\PHPCPD\CodeCloneMap
 * @uses \SebastianBergmann\PHPCPD\CodeCloneMapIterator
 */
final class GithubTest extends TestCase
{
    public function testSubstitutesDisallowedCharacters(): void
    {
        $testFile1 = \dirname(__DIR__).'/fixture/with_ascii_escape.php';
        $testFile2 = \dirname(__DIR__).'/fixture/with_ascii_escape2.php';
        $file1 = new CodeCloneFile($testFile1, 8);
        $file2 = new CodeCloneFile($testFile2, 8);
        $clone = new CodeClone($file1, $file2, 4, 4);
        $cloneMap = new CodeCloneMap();

        $cloneMap->add($clone);

        $githubLogger = new Github();
        ob_start();
        $githubLogger->processClones($cloneMap);
        $output = ob_get_clean();

        $this->assertEquals(
            <<<'EOF'
::error file=tests/fixture/with_ascii_escape.php,line=8,endline=12::Duplicated code detected
::error file=tests/fixture/with_ascii_escape2.php,line=8,endline=12::Duplicated code detected
EOF,
            trim($output)
        );
    }
}
