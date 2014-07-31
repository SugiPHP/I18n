<?php
/**
 * @package    SugiPHP
 * @subpackage I18n
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\I18n;

/**
 * Gettext Driver Interface
 */
interface DriverInterface
{
	/**
	 * Set locale information.
	 *
	 * @param string $locale
	 */
	public function setLocale($locale);

	/**
	 * Get locale information.
	 *
	 * @return string
	 */
	public function getLocale();

	/**
	 * Specify the directory where translation files for domain are.
	 *
	 * @param string $directory
	 */
	public function setPath($directory);

	/**
	 * Lookup a message in the current domain.
	 *
	 * @param  string $message The message being translated.
	 * @return string Returns a translated string if one is found in the
	 *                translation table, or the submitted message if not found.
	 */
	public function gettext($message);

	/**
	 * Plural version of gettext
	 *
	 * @param  string $msgid1
	 * @param  string $msgid2
	 * @param  integer $n
	 * @return Returns correct plural form of message identified by msgid1 and msgid2 for count n.
	 */
	public function ngettext($msgid1, $msgid2, $n);

	/**
	 * Sets the default domain.
	 * This function sets the domain to search within when calls are made to gettext(),
	 * usually the named after an application.
	 *
	 * @param  string $$text_domain The new message domain, or NULL to get the current setting without changing it
	 * @return string If successful, this function returns the current message domain, after possibly changing it.
	 */
	// public function textdomain($text_domain);

	/**
	 * Overrides the domain for a single lookup.
	 *
	 * @param  string  $domain The domain
	 * @param  string  $message The message
	 * @param  integer $category The category. Valid types are:
	 *                 LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES and LC_ALL.
	 * @return string A string on success.
	 */
	// public function dcgettext($domain, $message, $category);

	/**
	 * Plural version of dcgettext.
	 * This function allows you to override the current domain for a single plural message lookup.
	 *
	 * @param  string $domain The domain
	 * @param  string $msgid1
	 * @param  string $msgid2
	 * @param  integer $n
	 * @param  integer $category
	 * @return string A string on success.
	 */
	// public function dcngettext($domain, $msgid1, $msgid2, $n, $category);

	/**
	 * Override the current domain.
	 * The dgettext() function allows you to override the current domain for a single message lookup.
	 *
	 * @param  string $domain The domain
	 * @param  string $message The message
	 * @return string A string on success.
	 */
	// public function dgettext($domain, $message);

	/**
	 * Plural version of dgettext.
	 * The dngettext() function allows you to override the current domain for a single plural message lookup.
	 *
	 * @param  string $domain The domain
	 * @param  string $msgid1
	 * @param  string $msgid2
	 * @param  integer $n
	 * @return string A string on success.
	 */
	// public function dngettext($domain, $msgid1, $msgid2, $n);
}
