<?php
/**
 * @package    SugiPHP
 * @subpackage I18n
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\I18n;

/**
 * Gettext .mo files driver
 */
class MoDriver implements DriverInterface
{
	protected $path;
	protected $locale;
	protected $translations = array();

	/**
	 * Meta info in .mo files
	 */
	protected $meta = array();

	public function setLocale($locale)
	{
		$this->locale = $locale;

		return $this->locale;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setPath($directory)
	{
		$real = realpath($directory);
		if (is_dir($real)) {
			$this->path = $real;

			return $real;
		}

		return false;
	}

	public function gettext($message)
	{
		$trans = $this->getArray();

		return empty($trans[$message][0]) ? $message : $trans[$message][0];
	}

	public function ngettext($msgid1, $msgid2, $n)
	{
		// simplest check for plural form
		$plural = ($n == 1) ? 0 : 1;
		// check plural settings from meta info
		if (preg_match('#plural=(.*)$#iU', $this->getMetaInfo($this->locale, "Plural-Forms", "plural=(n != 1);"), $matches)) {
			$pluralDef = '$plural = '.preg_replace('#n#', '$n', $matches[1]);
			eval($pluralDef);
		}

		// returned message if no translations found
		$default = ($n == 1) ? $msgid1 : $msgid2;

		$trans = $this->getArray();
		// header part of the mo file.
		if (!empty($trans[""])) {
			// find is it plural or not from header
				// find what plural index should we use
		}

		// do we have that plural index?
		if (!empty($trans[$msgid1][$plural])) {
			return $trans[$msgid1][$plural];
		}

		// use available index
		return empty($trans[$msgid1][0]) ? $default : $trans[$msgid1][0];
	}

	public function getMetaInfo($locale, $metaName, $default = null)
	{
		return isset($this->meta[$locale][$metaName]) ? $this->meta[$locale][$metaName] : $default;
	}

	protected function getArray()
	{
		$locale = $this->getLocale();
		if (!isset($this->translations[$locale])) {
			$this->translations[$locale] = array();
			if ($file = $this->getFile()) {
				$this->translations[$locale] = $this->parseFile($file);
				$this->parseMetaInfo($locale);
			} else {
				$this->translations[$locale] = array();
			}
		}

		return $this->translations[$locale];
	}

	protected function parseMetaInfo($locale)
	{
		if (!empty($this->translations[$locale][""][0])) {
			$str = $this->translations[$locale][""][0];
			$arr = explode("\n", $str);
			foreach ($arr as $line) {
				if ($line) {
					$a = explode(":", $line, 2);
					$this->meta[$locale][trim($a[0])] = empty($a[1]) ? "" : trim($a[1]);
				}
			}

			unset($this->translations[$locale][""]);
		}
	}

	protected function getFile()
	{
		$locale = $this->getLocale();
		if (isset($this->path)) {
			$dir = $this->path.DIRECTORY_SEPARATOR;
			$dir .= $locale.".utf8".DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR;
			$file = "{$dir}messages.mo";
			if (file_exists($file)) {
				return $file;
			}
		}

		return false;
	}



	/*-------------------------
	 *   MO PARSER FUNCTIONS
	 *-------------------------*/

	/**
	 * Parses the MO file.
	 *
	 * @param  string $file File's name to be parsed.
	 * @return array Returns NULL on any error
	 */
	public function parseFile($file)
	{
		if (!$file) {
			throw new DriverException("Filename cannot be empty");
		}

		if (!file_exists($file)) {
			throw new DriverException("File $file does not exists");
		}

		if (!$filesize = filesize($file)) {
			throw new DriverException("File $file is empty");
		}

		if ($filesize < 32) {
			throw new DriverException("File $file is not a real .mo file. File too small.");
		}

		$fh = fopen($file, "rb");
		if ($fh === false) {
			throw new DriverException("File $file cannot be opened.");
		}
		$data = fread($fh, 8);
		$header = unpack("lmagic/lrevision", $data);

		$data = fread($fh, 20);
		$offsets = unpack("lnum_strings/lorig_offset/ltrans_offset/lhash_size/lhash_offset", $data);

		if (is_null($offsets)) {
			fclose($fh);
			throw new DriverException("File $file is not a real .mo file. Wrong offsets.");
		}

		$transTable = array();
		$table = $this->parseOffsetTable($fh, $offsets["trans_offset"], $offsets["num_strings"]);
		if (is_null($table)) {
			fclose($fh);
			throw new DriverException("File $file is not a real .mo file. Wrong translation offset.");
		}
		foreach ($table as $idx => $entry) {
			$transTable[$idx] = $this->parseEntry($fh, $entry);
		}

		$table = $this->parseOffsetTable($fh, $offsets["orig_offset"], $offsets["num_strings"]);
		$result = array();
		foreach ($table as $idx => $entry) {
			$entry = $this->parseEntry($fh, $entry);
			$forms = explode(chr(0), $entry);
			$translation = explode(chr(0), $transTable[$idx]);
			$result[$forms[0]] = $translation;
		}

		fclose($fh);

		return $result;
	}

	/**
	 * Parse and returns the string offsets in a a table. Two table can be found in
	 * a mo file. The table with the translations and the table with the original
	 * strings. Both contain offsets to the strings in the file.
	 *
	 * If an exception occurred, null is returned. This is intentionally
	 * as we need to get close to ext/gettexts behavior.
	 *
	 * @param pointer $hfile File handler
	 * @param integer $offset The offset to the table that should be parsed
	 * @param integer $num The number of strings to parse
	 * @return array of offsets
	 */
	protected function parseOffsetTable($hfile, $offset, $num)
	{
		if (fseek($hfile, $offset, SEEK_SET) < 0) {
			return null;
		}

		$table = array();
		for ($i = 0; $i < $num; $i++) {
			$data    = fread($hfile, 8);
			$table[] = unpack("lsize/loffset", $data);
		}

		return $table;
	}

	/**
	 * Parse a string as referenced by an table. Returns an
	 * array with the actual string.
	 *
	 * @param  pointer $hfile File handler to the MO fie
	 * @param  array $entry The entry as parsed by parseOffsetTable()
	 * @return string
	 */
	private function parseEntry($hfile, $entry)
	{
		if (fseek($hfile, $entry["offset"], SEEK_SET) < 0) {
			return null;
		}
		if ($entry["size"] > 0) {
			return fread($hfile, $entry["size"]);
		}

		return "";
	}
}
