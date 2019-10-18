<?php
/**
 * Created by PhpStorm.
 * User: bhavikji
 * Date: 29/9/17
 * Time: 5:33 PM
 */

namespace App\Utils;


class GetTokens {
	/**
	 * @var
	 */
	public $prefix;

	/**
	 * @var
	 */
	public $entropy;

	/**
	 * @param string $prefix
	 * @param bool $entropy
	 */
	public function __construct($prefix = '', $entropy = false)
	{
		$this->token = uniqid($prefix, $entropy);
	}

	/**
	 * Limit the Token by a number of characters
	 *
	 * @param $length
	 * @param int $start
	 * @return $this
	 */
	public function limit($length, $start = 0)
	{
		$this->token = substr($this->token, $start, $length);

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->token;
	}
}