<?php
namespace dnj\IsoMaker;

use SplFileInfo;
use SplFileObject;
use DirectoryIterator;
use InvalidArgumentException;
use dnj\IsoMaker\Exception;
use dnj\IsoMaker\Exception\{NotShellAccess};
use dnj\IsoMaker\Contracts\{ICustomization, IOperatingSystem};

abstract class IsoMaker
{
	/**
	 * @var IOperatingSystem $os;
	 */
	protected $os;

	/**
	 * @var SplFileObject original ISO File which should not modify
	 */
	protected $orignalISO;

	/**
	 * @var array<string, mixed> $options
	 */
	protected $options = [
		'shell_interface' => 'builtin',
		'ssh' => null,
	];

	/**
	 * @var bool $shellTest that indicates can execute command
	 */
	protected $shellTest = false;

	/**
	 * @param IOperatingSystem $os
	 * @param array<string,mixed> $options
	 */
	public function __construct(IOperatingSystem $os, array $options = [])
	{
		$this->os = $os;
		$this->options = $options;
		$this->orignalISO = $this->getIsoFile();
	}

	public function getIsoFile(): SplFileObject
	{
		return $this->os->getISOFile();
	}

	/**
	 * Customize a general OS ISO image to make unattend installation and other customization.
	 * 
	 * @param ICustomization $customization
	 * @return array<SplFileObject> that are iso file you should mount in your device
	 */
	public function customizeForCustomization(ICustomization $customization): array
	{
		$dir = $this->unpackISO($this->orignalISO);

		return $this->applyCustomization($customization, $dir);
	}

	/**
	 * Pack a directory into an bootable ISO file.
	 * 
	 * @abstract
	 * @param DirectoryIterator $directory
	 * @param SplFileInfo $iso new ISO
	 * @return void 
	 */
	abstract protected function packISO(DirectoryIterator $directory, SplFileInfo $iso): void;

	/**
	 * Apply changes to a unpacked ISO
	 * 
	 * @abstract
	 * @param ICustomization $customization
	 * @param DirectoryIterator $directory
	 * @return SplFileObject[] of ISO files
	 */
	abstract protected function applyCustomization(ICustomization $customization, DirectoryIterator $directory): array;

	/**
	 * Extract files from ISO to anthor working directory.
	 * 
	 * @param SplFileObject $iso
	 * @throws NotShellAccess if shell_exec() function is disabled
	 * @return DirectoryIterator working directory for changing the files of ISO
	 */
	protected function unpackISO(SplFileObject $iso): DirectoryIterator {
		$chars = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM123456789";
		$sysTempDir = sys_get_temp_dir();
		do {
			$dirPath = $sysTempDir . '/' . substr(str_shuffle($chars), 0, rand(5,10));

		} while(is_dir($dirPath));

		$repo = new DirectoryIterator($dirPath);

		$this->insureCommand("7z");
		// there is NO SPACE between -o and the media path
		$this->runCommand("7z x -y " . $iso->getRealPath() . " -o" . $repo->getRealPath() . "/ 2>&1");

		$isEmpty = true;
		foreach ($repo as $item) {
			if ($item->isDot()) {
				continue;
			}
			$isEmpty = false;
			break;
		}
		if ($isEmpty) {
			throw new Exception("something is wrong in extract iso: " . $iso->getRealPath());
		}

		return $repo;
	}

	/**
	 * Get filesystem label from ISO file using isoinfo command.
	 * 
	 * @param SplFileInfo $file ISO file
	 * @throws InvalidArgumentException if given SplFileInfo $file is not file
	 * @throws Exception if cannot find isoinfo command using which command
	 * @throws Exception cannot find ISO label
	 * @return string filesystem label
	 */
	protected function getISOLabel(SplFileInfo $file): string {
		if (!$file->isFile()) {
			throw new InvalidArgumentException('The passed SplFileInfo is not file!');
		}
		$this->insureCommand("isoinfo");
		$result = $this->runCommand("isoinfo -d -i " . $file->getRealPath());

		if (!preg_match("/Volume id:\s+(.+)\s*/im", $result, $matches)) {
			throw new Exception("cannot find ISO label");
		}
		return $matches[1];
	}

	/**
	 * Implant an MD5 checksum in an ISO9660 image
	 * 
	 * @param SplFileObject $file ISO file
	 * @throws Exception if cannot find implantisomd5 command using which command
	 * @return void
	 */
	protected function ISOmd5(SplFileObject $file): void {
		$this->insureCommand("implantisomd5");
		$this->runCommand("implantisomd5 " . $file->getRealPath());
	}

	/**
	 * Insure existice of a command in ENV.
	 * 
	 * @param string $command
	 * @throws Exception if cannot find the command.
	 * @return void
	 */
	protected function insureCommand(string $command): void
	{
		$result = $this->runCommand("which {$command}");
		if (!$result or stripos($result, "not found") !== false) {
			throw new Exception($result);
		}
	}

	/**
	 * Run Command on local server and return the result.
	 * Please note that the commands may run using SSH or shell_exec() function.
	 * 
	 * @param string $cmd command to execute
	 * @throws NotShellAccess if shell_interface was "builtin" and shell_exec() function is disabled
	 * @return string result of command
	 */
	protected function runCommand(string $cmd): string
	{
		$result = null;
		if ($this->options['shell_interface'] == "builtin") {
			if (!$this->shellTest and !function_exists('shell_exec')) {
				throw new NotShellAccess();
			}
			$this->shellTest = true;
			$result = shell_exec($cmd);
		} elseif ($this->options['shell_interface'] == "ssh") {
			$result = $this->options['ssh']->execute($cmd);
		}
		return (string)$result;
	}
}
