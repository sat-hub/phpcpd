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

use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<CodeClone>
 */
final class CodeCloneMap implements \Countable, \IteratorAggregate
{
    /**
     * @var CodeClone[]
     */
    private array $clones = [];

    /**
     * @var CodeClone[]
     */
    private array $clonesById = [];

    private int $numberOfDuplicatedLines = 0;

    private int $numberOfLines = 0;

    private int $largestCloneSize = 0;

    private array $filesWithClones = [];

    public function add(CodeClone $clone): void
    {
        $id = $clone->id();

        if (!isset($this->clonesById[$id])) {
            $this->clones[] = $clone;
            $this->clonesById[$id] = $clone;
        } else {
            $existClone = $this->clonesById[$id];

            foreach ($clone->files() as $file) {
                $existClone->add($file);
            }
        }

        $this->numberOfDuplicatedLines += $clone->numberOfLines() * (\count($clone->files()) - 1);

        foreach ($clone->files() as $file) {
            if (!isset($this->filesWithClones[$file->name()])) {
                $this->filesWithClones[$file->name()] = true;
            }
        }

        $this->largestCloneSize = max($this->largestCloneSize, $clone->numberOfLines());
    }

    /**
     * @return CodeClone[]
     */
    public function clones(): array
    {
        return $this->clones;
    }

    public function percentage(): string
    {
        $percent = $this->numberOfLines > 0 ? ($this->numberOfDuplicatedLines / $this->numberOfLines) * 100 : 100;

        return sprintf('%01.2F%%', $percent);
    }

    public function numberOfLines(): int
    {
        return $this->numberOfLines;
    }

    public function addToNumberOfLines(int $numberOfLines): void
    {
        $this->numberOfLines += $numberOfLines;
    }

    public function count(): int
    {
        return \count($this->clones);
    }

    public function numberOfFilesWithClones(): int
    {
        return \count($this->filesWithClones);
    }

    public function numberOfDuplicatedLines(): int
    {
        return $this->numberOfDuplicatedLines;
    }

    public function getIterator(): CodeCloneMapIterator
    {
        return new CodeCloneMapIterator($this);
    }

    public function isEmpty(): bool
    {
        return [] === $this->clones;
    }

    public function averageSize(): float
    {
        return $this->numberOfDuplicatedLines() / $this->count();
    }

    public function largestSize(): int
    {
        return $this->largestCloneSize;
    }
}
