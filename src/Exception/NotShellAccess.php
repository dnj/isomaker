<?php
namespace dnj\IsoMaker\Exception;

use dnj\IsoMaker\Exception;

class NotShellAccess extends Exception
{
	public function __construct(string $message = "shell_exec() function is disabled on this system")
	{
		parent::__construct($message);
	}
}
