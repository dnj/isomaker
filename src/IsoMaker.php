<?php

namespace dnj\IsoMaker;

use dnj\Filesystem\Contracts\IDirectory;
use dnj\Filesystem\Contracts\IFile;
use dnj\Filesystem\Tmp;
use dnj\IsoMaker\Contracts\ICustomization;
use dnj\IsoMaker\Contracts\IOperatingSystem;
use dnj\IsoMaker\Exception\{NotShellAccess};
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

abstract class IsoMaker
{
    protected IOperatingSystem $os;

    /**
     * @var array<string,mixed>
     */
    protected array $options = [];

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(IOperatingSystem $os, array $options = [])
    {
        $this->os = $os;
        $this->options = array_replace_recursive($this->options, $options);
    }

    /**
     * Customize a general OS ISO image to make unattend installation and other customization.
     *
     * @return IFile[] output iso files that you should mount in your device
     */
    abstract public function customize(ICustomization $customization): array;

    /**
     * Pack a directory into an bootable ISO file.
     *
     * @param IFile $iso new ISO
     */
    abstract protected function packISO(IDirectory $directory, IFile $iso): void;

    /**
     * Extract files from ISO to anthor working directory.
     *
     * @return IDirectory working directory for changing the files of ISO
     */
    protected function unpackISO(IFile $iso): IDirectory
    {
        $this->insureCommand('7z');

        $repo = new Tmp\Directory();

        // there is NO SPACE between -o and the media path
        $this->runCommand(['7z', 'x', '-y', $iso->getPath(), '-o'.$repo->getRealPath()]);

        if ($repo->isEmpty()) {
            throw new Exception('something is wrong in extract iso: '.$iso->getPath());
        }

        return $repo;
    }

    /**
     * Get filesystem label from ISO file using isoinfo command.
     *
     * @param IFile $file ISO file
     *
     * @throws InvalidArgumentException if given IFile $file is not file
     * @throws Exception                if cannot find isoinfo command using which command
     * @throws Exception                cannot find ISO label
     *
     * @return string filesystem label
     */
    protected function getISOLabel(IFile $file): string
    {
        if (!$file->exists()) {
            throw new InvalidArgumentException('The passed file is not file!');
        }
        $this->insureCommand('isoinfo');
        $result = $this->runCommand(['isoinfo', '-d', '-i', $file->getPath()]);

        if (!preg_match("/Volume id:\s+(.+)\s*/im", $result, $matches)) {
            throw new Exception('cannot find ISO label');
        }

        return $matches[1];
    }

    /**
     * Implant an MD5 checksum in an ISO9660 image.
     *
     * @param IFile $file ISO file
     *
     * @throws Exception if cannot find implantisomd5 command using which command
     */
    protected function ISOmd5(IFile $file): void
    {
        $this->insureCommand('implantisomd5');
        $this->runCommand(['implantisomd5', '--force', $file->getPath()]);
    }

    /**
     * Insure existice of a command in ENV.
     *
     * @throws Exception if cannot find the command
     */
    protected function insureCommand(string $command): void
    {
        $finder = new ExecutableFinder();
        if (!$finder->find($command)) {
            throw new Exception("Cannot find executable for {$command}");
        }
    }

    /**
     * Run Command on local server and return the result.
     *
     * @param string[] $cmd command to execute
     *
     * @throws ProcessFailedException if the process didn't terminate successfully
     *
     * @return string result of command
     */
    protected function runCommand(array $cmd): string
    {
        $process = new Process($cmd);
        $process->mustRun();

        return $process->getOutput();
    }
}
