<?php
namespace dnj\IsoMaker\Contracts;

interface ICustomization
{
	public function getNetwork(): ?Network;

	public function getPassword(): Password;

}