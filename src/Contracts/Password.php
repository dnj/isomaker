<?php
namespace dnj\IsoMaker\Contracts;

use InvalidArgumentException;

class Password
{

	public static function generate(int $length = 10, bool $number = true, bool $az = true, bool $AZ = true, bool $special = false): Password
	{
		$parts = 0;
		
        if ($number) {
			$parts++;
		}
        if ($az) {
			$parts++;
		}
        if ($AZ) {
			$parts++;
		}
		if ($special) {
			$parts++;
		}

		if ($parts === 0) {
			throw new InvalidArgumentException('you should pass true for at least one argument!');
		}

		$azChar = "abcdefghijklmnopqrstuvwxyz";
        $AZChar = strtoupper($azChar);
        $numberChar = "0123456789";
		$specialChar = ".-+=_,!@$#*%<>[]{}";

        $uses = [
            $numberChar => $number,
            $azChar => $az,
            $AZChar => $AZ,
            $specialChar => $special,
		];

        $password = "";
        for ($i = 0; $i != ceil($length / $parts); $i++) {
            foreach ($uses as $chars => $flag) {
                if (strlen($password) == $length) {
                    break;
                }
                if ($flag) {
                    $password .= substr($chars, rand(0, strlen($chars) -1), 1);
                }
            }
        }
        return new self($password);
    }

	/**
	 * @var string $password;
	 */
	protected $password;

	public function __construct(string $password)
	{
		if (empty($password)) {
			throw new InvalidArgumentException('The passed password should not be empty!');
		}
		$this->password = $password;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * Hash the password using SHA-512 algo.
	 * 
	 * @return string
	 */
	public function getSha512Password(): string
	{
		return crypt($this->password, '$6$' . self::generate(13)->getPassword());
	}
}