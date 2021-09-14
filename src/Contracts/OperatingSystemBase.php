<?php
namespace dnj\IsoMaker\Contracts;

use SplFileInfo;

class OperatingSystemBase implements IOperatingSystemBase
{
	/**
	 * @var int $base
	 */
	protected $base;

	/**
	 * @var string $bitness
	 */
	protected $bitness;

	public function __construct(int $base, string $bitness)
	{
		$this->base = $base;
		$this->bitness = $bitness;
	}

	public function getBase(): int
	{
		return $this->base;
	}

	public function getBitness(): string
	{
		return $this->bitness;
	}
}
