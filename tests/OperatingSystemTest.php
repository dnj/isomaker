<?php

namespace dnj\IsoMaker\Tests;

use dnj\IsoMaker\Contracts\Bitness;
use dnj\IsoMaker\OperatingSystem;
use PHPUnit\Framework\TestCase;

class OperatingSystemTest extends TestCase
{
    public function testMethods(): void
    {
        $os = new OperatingSystem('windows', Bitness::X64(), null);
        $this->assertEquals('windows', $os->getName());
        $this->assertTrue($os->getBitness()->equals(Bitness::X64()));
        $this->assertNull($os->getISOFile());
    }
}
