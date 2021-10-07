<?php

namespace dnj\IsoMaker\Tests;

use dnj\Filesystem\Local;
use dnj\Filesystem\Tmp;
use dnj\IsoMaker\Contracts\Bitness;
use dnj\IsoMaker\IsoMaker;
use dnj\IsoMaker\OperatingSystem;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Process\ExecutableFinder;

class IsoMakerTest extends TestCase
{
    public function testUnpackIso(): void
    {
        $this->insureCommand('7z');

        $unpackIso = $this->getMethod('unpackISO');

        $isoFile = $this->getISOFile();
        $os = new OperatingSystem('name', Bitness::X64(), $isoFile);

        $isoMaker = $this->getMockForAbstractClass(IsoMaker::class, [$os]);
        $result = $unpackIso->invokeArgs($isoMaker, [$isoFile]);
        $this->assertInstanceOf(Tmp\Directory::class, $result);
    }

    public function testGetISOLabel(): void
    {
        $this->insureCommand('isoinfo');

        $getISOLabel = $this->getMethod('getISOLabel');

        $isoFile = $this->getISOFile();
        $os = new OperatingSystem('name', Bitness::X64(), $isoFile);

        $isoMaker = $this->getMockForAbstractClass(IsoMaker::class, [$os]);
        $result = $getISOLabel->invokeArgs($isoMaker, [$isoFile]);
        $this->assertIsString($result);
    }

    public function testISOmd5(): void
    {
        $this->insureCommand('implantisomd5');

        $ISOmd5 = $this->getMethod('ISOmd5');

        $isoFile = $this->getISOFile();

        $tmp = new Tmp\Directory();
        $tmpISO = $tmp->file('os.iso');
        $isoFile->copyTo($tmpISO);

        $os = new OperatingSystem('name', Bitness::X64(), $tmpISO);

        $isoMaker = $this->getMockForAbstractClass(IsoMaker::class, [$os]);
        $ISOmd5->invokeArgs($isoMaker, [$tmpISO]);
        $this->assertTrue(true);
    }

    protected function getMethod(string $name): ReflectionMethod
    {
        $class = new ReflectionClass(IsoMaker::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    protected function getISOFile(): Local\File
    {
        $iso = getenv('ISOMAKER_ISO');
        if (!$iso) {
            $this->markTestSkipped('This test needs iso file (ISOMAKER_ISO)');
        }

        return new Local\File($iso);
    }

    protected function insureCommand(string $command): void
    {
        $finder = new ExecutableFinder();
        if (!$finder->find($command)) {
            $this->markTestSkipped("This test needs {$command} executable");
        }
    }
}
