<?php
namespace dnj\IsoMaker\Contracts;

use SplFileInfo;

interface IOperatingSystemBase
{
	/* base */
	const LINUX = 1000;

	const DEBIAN = 1001;
	const DEBIAN_9 = 1005;
	const CENTOS6 = 1002;
	const CENTOS7 = 1003;
	const UBUNTU = 1004;
	const UBUNTU_18 = 1006;
	const UBUNTU_20 = 1007;

	const WINDOWS = 2000;

	const WINDOWS_7 = 2001;
	const WINDOWS_SERVER_2008 = 2002;
	const WINDOWS_8 = 2003;
	const WINDOWS_SERVER_2012 = 2004;
	const WINDOWS_10 = 2005;
	const WINDOWS_SERVER_2016 = 2006;

	/* bitness */
	const X86 = '32bit';
	const X64 = '64';

	public function getBase(): int;

	public function getBitness(): string;
}
