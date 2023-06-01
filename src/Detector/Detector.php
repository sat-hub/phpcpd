<?php declare(strict_types=1);
/*
 * This file is part of PHP Copy/Paste Detector (PHPCPD).
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\PHPCPD\Detector;

use SebastianBergmann\PHPCPD\CodeCloneMap;
use SebastianBergmann\PHPCPD\Detector\Strategy\AbstractStrategy;
use Symfony\Component\Finder\Finder;

final class Detector
{
    private AbstractStrategy $strategy;

    public function __construct(AbstractStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function copyPasteDetection(Finder $files): CodeCloneMap
    {
        $result = new CodeCloneMap;

        foreach ($files as $file) {
            $this->strategy->processFile(
                $file->getRealPath(),
                $result
            );
        }

        $this->strategy->postProcess();

        return $result;
    }
}
