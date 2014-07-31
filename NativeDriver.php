<?php
/**
 * @package    SugiPHP
 * @subpackage I18n
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\I18n;

/**
 * Gettext Native Driver
 */
class NativeDriver implements DriverInterface
{
	protected $locale;

	public function setLocale($locale)
	{
		if ($res = setlocale(LC_ALL, $locale.".utf8")) {
			$this->locale = $locale;
		}

		return $res;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setPath($directory)
	{
		// Specify the character encoding in which the messages from the DOMAIN message catalog will be returned.
		bind_textdomain_codeset("messages", "utf8");
		// Set the path for a domain.
		bindtextdomain("messages", $directory);
		// Sets the default domain.
		textdomain("messages");
	}

	public function gettext($message)
	{
		return gettext($message);
	}

	public function ngettext($msgid1, $msgid2, $n)
	{
		return ngettext($msgid1, $msgid2, $n);
	}


	// public function dcgettext($domain, $message, $category)
	// {
	// 	return dcgettext($domain, $message, $category);
	// }

	// public function dcngettext($domain, $msgid1, $msgid2, $n, $category)
	// {
	// 	return dcngettext($domain, $msgid1, $msgid2, $n, $category);
	// }

	// public function dgettext($domain, $message)
	// {
	// 	return dgettext($domain, $message);
	// }

	// public function dngettext($domain, $msgid1, $msgid2, $n)
	// {
	// 	return dngettext($domain, $msgid1, $msgid2, $n);
	// }

	// public function textdomain($text_domain)
	// {
	// 	return textdomain($text_domain);
	// }
}
