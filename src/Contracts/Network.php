<?php
namespace dnj\IsoMaker\Contracts;

use SplFileInfo;
use RuntimeException;
use InvalidArgumentException;

abstract class Network {

	/**
	 * method validIP.
	 * Determine if a given input is a valid IPv4 address.
	 * Usage:
	 *     CIDR::validIP('0.50.45.50');
	 * Result:
	 *     bool(false)
	 * @param string $ipinput String a IPv4 formatted ip address.
	 * @return bool
	 */
	public static function validIP(string $ipinput): bool
	{
		return boolval(filter_var($ipinput, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
	}

	/**
	 * method CIDRtoMask
	 * Return a netmask string if given an integer between 0 and 32. I am 
	 * not sure how this works on 64 bit machines.
	 * Usage:
	 *     CIDR::CIDRtoMask(22);
	 * Result:
	 *     string(13) "255.255.252.0"
	 * @param int $int Between 0 and 32.
	 * @return string Netmask ip address
	 */
	public static function CIDRtoMask(int $int): string
	{
		$result = long2ip(-1 << (32 - (int)$int));
		if ($result === false) {
			throw new RuntimeException('can not calulate CIDRtoMask');
		}
		return $result;
	}

	/**
	 * method maskToCIDR.
	 * Return a CIDR block number when given a valid netmask.
	 * Usage:
	 *     CIDR::maskToCIDR('255.255.252.0');
	 * Result:
	 *     int(22)
	 * @param string $netmask String a 1pv4 formatted ip address.
	 * @return int CIDR number.
	 */
	public static function maskToCIDR(string $netmask): int
	{
		if (self::validNetMask($netmask)) {
			$ip2long = ip2long($netmask);
			if ($ip2long === false) {
				throw new RuntimeException('can not calulate maskToCIDR');
			}

			return self::countSetBits($ip2long);
		}
		throw new InvalidArgumentException('Invalid netmask');
	}

	/**
	 * method validNetMask.
	 * Determine if a string is a valid netmask.
	 * Usage:
	 *     CIDR::validNetMask('255.255.252.0');
	 *     CIDR::validNetMask('127.0.0.1');
	 * Result:
	 *     bool(true)
	 *     bool(false)
	 * @param string $netmask a 1pv4 formatted ip address.
	 * @see http://www.actionsnip.com/snippets/tomo_atlacatl/calculate-if-a-netmask-is-valid--as2-
	 * @return bool True if a valid netmask.
	 */
	public static function validNetMask(string $netmask): bool
	{
		$netmask = ip2long($netmask);
		if($netmask === false) return false;
		$neg = ((~(int)$netmask) & 0xFFFFFFFF);
		return (($neg + 1) & $neg) === 0;
	}

	/**
	 * method countSetBits.
	 * Return the number of bits that are set in an integer.
	 * Usage:
	 *     CIDR::countSetBits(ip2long('255.255.252.0'));
	 * Result:
	 *     int(22)
	 * @param int $int a number
	 * @see http://stackoverflow.com/questions/109023/best-algorithm-to-co\
	 * unt-the-number-of-set-bits-in-a-32-bit-integer
	 * @return int number of bits set.
	 */
	public static function countSetbits(int $int): int
	{
		$int = $int & 0xFFFFFFFF;
		$int = ( $int & 0x55555555 ) + ( ( $int >> 1 ) & 0x55555555 ); 
		$int = ( $int & 0x33333333 ) + ( ( $int >> 2 ) & 0x33333333 );
		$int = ( $int & 0x0F0F0F0F ) + ( ( $int >> 4 ) & 0x0F0F0F0F );
		$int = ( $int & 0x00FF00FF ) + ( ( $int >> 8 ) & 0x00FF00FF );
		$int = ( $int & 0x0000FFFF ) + ( ( $int >>16 ) & 0x0000FFFF );
		$int = $int & 0x0000003F;
		if (!is_int($int)) {
			throw new RuntimeException('can not calulate countSetbits');
		}
		return $int;
	}

	/**
	 * method alignedCIDR.
	 * It takes an ip address and a netmask and returns a valid CIDR
	 * block.
	 * Usage:
	 *     CIDR::alignedCIDR('127.0.0.1','255.255.252.0');
	 * Result:
	 *     string(12) "127.0.0.0/22"
	 * @param string $ipinput String a IPv4 formatted ip address.
	 * @param string $netmask String a 1pv4 formatted ip address.
	 * @return string CIDR block.
	 */
	public static function alignedCIDR(string $ipinput, string $netmask): string
	{
		$alignedIP = long2ip((ip2long($ipinput)) & (ip2long($netmask)));
		return "$alignedIP/" . self::maskToCIDR($netmask);
	}

	/**
	 * check a mac address is a valid mac address?
	 * @param string $value
	 * @return bool true if valid, else false
	 */
	public static function isValidMacAddress(string $value): bool
	{
		return boolval(preg_match(
            "/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/", $value
        ));
	}

	/**
	 * @var string[] $dnses
	 */
	protected array $dnses = [
		'8.8.8.8',
		'1.1.1.1',
	];

	/**
	 * If network using DHCP protocol to assing ip, we skip get ip related information, else, we use below methods to config network
	 * @return bool that if true, network is using DHCP
	 */
	abstract public function isUsingDHCP(): bool;

	/**
	 * Get the IP address, like: 192.168.1.1
	 * @return string
	 */
	abstract public function getIPAddress(): string;

	abstract public function setIPAddress(string $ip): void;

	abstract public function getNetmaskAddress(): string;

	abstract public function setNestmaskAddress(string $netmask): void;

	abstract public function getGatewayAddress(): string;
	
	abstract public function setGatewayAddress(string $gateway): string;

	abstract public function getMacAddress(): ?string;

	abstract public function setMacAddress(?string $macAddress): void;

	/**
	 * @return string[] the DNSes array, usually there are 2 dns in array, no need more
	 */
	public function getDNSes(): array
	{
		return $this->dnses;
	}

	/**
	 * @param string[] $dnses
	 */
	public function setDNSes(array $dnses): void
	{
		$this->dnses = $dnses;
	}

	public function addDNS(string $dns): void
	{
		if (!in_array($dns, $this->dnses)) {
			$this->dnses[] = $dns;
		}
	}
}
