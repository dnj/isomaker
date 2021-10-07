<?php

namespace dnj\IsoMaker;

use dnj\Filesystem\Contracts\IFile;
use dnj\IsoMaker\Contracts\Bitness;
use dnj\IsoMaker\Contracts\IOperatingSystem;

class OperatingSystem implements IOperatingSystem
{
    protected string $name;
    protected Bitness $bitness;
    protected ?IFile $isoFile = null;

    public function __construct(string $name, Bitness $bitness, ?IFile $isoFile = null)
    {
        $this->name = $name;
        $this->bitness = $bitness;
        $this->isoFile = $isoFile;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBitness(): Bitness
    {
        return $this->bitness;
    }

    public function getISOFile(): ?IFile
    {
        return $this->isoFile;
    }
}
