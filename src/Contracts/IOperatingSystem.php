<?php
namespace dnj\IsoMaker\Contracts;

use SplFileObject;

interface IOperatingSystem
{
	public function setName(string $name): void;

	public function getName(): string;

	public function setISOFile(SplFileObject $file): void;

	public function getISOFile(): SplFileObject;

	public function setBase(IOperatingSystemBase $base): void;

	public function getBase(): IOperatingSystemBase;

}
