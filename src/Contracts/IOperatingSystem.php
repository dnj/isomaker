<?php

namespace dnj\IsoMaker\Contracts;

use dnj\Filesystem\Contracts\IFile;

interface IOperatingSystem
{
    public function getName(): string;

    public function getISOFile(): ?IFile;

    public function getBitness(): Bitness;
}
