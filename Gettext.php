<?php
/**
 * @package    SugiPHP
 * @subpackage I18n
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\I18n;

use IntlDateFormatter;

/**
 * Gettext Class
 */
class Gettext
{
	protected $driver;

	/**
	 * Gettext constructor.
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		if (!isset($config["driver"])) {
			throw new \Exception("Gettext driver not specified.");
		}

		if (!isset($config["path"])) {
			throw new \Exception("Translation files path must be set");
		}

		foreach (array("driver", "path") as $key) {
			if (!empty($config[$key])) {
				$func = "set".ucfirst($key);
				$this->$func($config[$key]);
			}
		}

		if (!isset($config["locale"])) {
			// default locale is set in PHP
			$this->setLocale(setlocale(LC_ALL, 0));
		} else {
			$this->setLocale($config["locale"]);
		}
	}

	public function setDriver($driver)
	{
		if (!$driver instanceof DriverInterface) {
			throw new \Exception("Driver must be instanceof DriverInterface");
		}

		$this->driver = $driver;
	}

	public function getDriver()
	{
		return $this->driver;
	}

	public function setPath($path)
	{
		$this->driver->setPath($path);
	}

	public function setLocale($locale)
	{
		$this->driver->setLocale($locale);
	}

	public function getLocale()
	{
		return $this->driver->getLocale();
	}

	public function gettext($message)
	{
		return $this->driver->gettext($message);
	}

	/**
	 * Translates and formats message.
	 *
	 * @param  string $message
	 * @return string
	 */
	public function gettextf($message)
	{
		$message = $this->driver->gettext($message);

		if (func_num_args() === 1) {
			return $message;
		}

		$spargs = array_slice(func_get_args(), 1);
		if (!is_array($spargs[0])) {
			return vsprintf($message, $spargs);
		}

		foreach ($spargs[0] as $key => $value) {
			$message = str_replace('{'.$key.'}', $value, $message);
		}

		return $message;
	}

	public function ngettext($msgid1, $msgid2, $n)
	{
		return $this->driver->ngettext($msgid1, $msgid2, $n);
	}

	/**
	 * Translates plural form messages and formats output.
	 *
	 * If only 3 parameters are given it works like ngettext()
	 * If 4th parameter is an array it will substitute "{key}" => value
	 * like in the example:
	 *   ngettextf("{name} has {cnt} strawberry", "{name} has {cnt} strawberries", 3, ['cnt' => 3, 'name' => 'John']);
	 *   will return John has 3 strawberries
	 *
	 * if 4th parameter is not array it will use sprintf for all remaining parameters
	 * like in the example:
	 *   ngettextf("%s has %d apple", "%s has %d apples", 1, "Peter", 1); // will return Peter has 1 apple
	 *
	 * @param  string $msgid1 Message in singular form
	 * @param  string $msgid2 Message in plural form
	 * @param  integer $n Number which is used to check singular or plural form to use
	 * @return string
	 */
	public function ngettextf($msgid1, $msgid2, $n)
	{
		$message = $this->ngettext($msgid1, $msgid2, $n);
		if (func_num_args() === 3) {
			return $message;
		}

		$spargs = array_slice(func_get_args(), 3);
		if (!is_array($spargs[0])) {
			return vsprintf($message, $spargs);
		}

		foreach ($spargs[0] as $key => $value) {
			$message = str_replace('{'.$key.'}', $value, $message);
		}

		return $message;
	}

	public function formatDate($dateFmt, $timeFmt, $timestamp = null)
	{
		static $formats = array(
			"none"    => IntlDateFormatter::NONE,
			"short"   => IntlDateFormatter::SHORT,
			"medium"  => IntlDateFormatter::MEDIUM,
			"long"  => IntlDateFormatter::LONG,
			"full"    => IntlDateFormatter::FULL,
		);

		if (is_null($timestamp)) {
			$timestamp = time();
		}

		return IntlDateFormatter::create($this->getLocale(), $formats[$dateFmt], $formats[$timeFmt])->format($timestamp);
	}

	public function getDatePattern($dateFmt, $timeFmt)
	{
		static $formats = array(
			"none"    => IntlDateFormatter::NONE,
			"short"   => IntlDateFormatter::SHORT,
			"medium"  => IntlDateFormatter::MEDIUM,
			"long"  => IntlDateFormatter::LONG,
			"full"    => IntlDateFormatter::FULL,
		);

		$idf = IntlDateFormatter::create($this->getLocale(), $formats[$dateFmt], $formats[$timeFmt]);
		return $idf->getPattern();
	}


	// /**
	//  * Returns formatted text from the gettext library by message ID using sprintf
	//  *
	//  * @param  string $msgid Message ID
	//  * @return mixed
	//  */
	// public function gettextf($msgid)
	// {
	// 	$args = func_get_args();
	// 	$args[0] = gettext($msgid);

	// 	return call_user_func_array("sprintf", $args);
	// }

	// /**
	//  * Returns formatted plural text (i.e. 1 button, 2 buttons) using sprintf
	//  *
	//  * @param  string  $msgid1 Message in one amount
	//  * @param  string  $msgid2 Message in two amounts
	//  * @param  integer $n Plural count
	//  * @return string
	//  */
	// public function ngettextf($msgid1, $msgid2, $n)
	// {
	// 	$args = func_get_args();
	// 	array_splice($args, 0, 2, array(ngettext($msgid1, $msgid2, $n)));

	// 	return call_user_func_array("sprintf", $args);
	// }

	// protected function bind_textdomain_codeset($domain, $codeset)
	// {
	// 	return bind_textdomain_codeset($domain, $codeset);
	// }

	// protected function bindtextdomain($domain, $directory)
	// {
	// 	return bindtextdomain($domain, $directory);
	// }

	// protected function dcgettext($domain, $message, $category)
	// {
	// 	return dcgettext($domain, $message, $category);
	// }

	// protected function dcngettext($domain, $msgid1, $msgid2, $n, $category)
	// {
	// 	return dcngettext($domain, $msgid1, $msgid2, $n, $category);
	// }

	// protected function dgettext($domain, $message)
	// {
	// 	return dgettext($domain, $message);
	// }

	// protected function dngettext($domain, $msgid1, $msgid2, $n)
	// {
	// 	return dngettext($domain, $msgid1, $msgid2, $n);
	// }
}
