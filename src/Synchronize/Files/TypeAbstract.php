<?php
namespace DasRed\PhraseApp\Synchronize\Files;

use DasRed\PhraseApp\Synchronize;
use DasRed\PhraseApp\Synchronize\Files\Exception\FailureCreateLocalePath;
use DasRed\PhraseApp\Synchronize\Files\Exception\InvalidPath;

abstract class TypeAbstract implements HandlerInterface
{

	/**
	 * exclude name parts of files
	 *
	 * @var array
	 */
	protected $excludeNames = [];

	/**
	 *
	 * @var string
	 */
	protected $path;

	/**
	 *
	 * @param string $path
	 * @param array $excludeNames
	 */
	public function __construct($path, $excludeNames = null)
	{
		$this->setPath($path);

		if ($excludeNames !== null)
		{
			$this->setExcludeNames($excludeNames);
		}
	}

	/**
	 *
	 * @return array
	 */
	protected function collectFiles()
	{
		$directory = new \RecursiveDirectoryIterator($this->getPath());
		$iterator = new \RecursiveIteratorIterator($directory);
		$regex = new \RegexIterator($iterator, '/^.+\.' . preg_quote($this->getFileExtension(), '/') . '$/i', \RecursiveRegexIterator::GET_MATCH);

		$result = [];
		foreach ($regex as $file)
		{
			$filePart = rtrim(str_replace([
				'\\',
				$this->getPath()
			], [
				'/',
				''
			], $file[0]), '/');
			$parts = explode('/', $filePart, 2);

			// key name
			$name = (strpos($parts[1], '/') !== false ? dirname($parts[1]) . '/' : '') . basename($parts[1], '.' . $this->getFileExtension());
			$found = false;
			foreach ($this->getExcludeNames() as $regex)
			{
				if (preg_match('#' . $regex . '#i', $name) != 0)
				{
					$found = true;
					break;
				}
			}
			if ($found === true)
			{
				continue;
			}

			$result[] = [
				'file' => $this->getPath() . $filePart,
				'locale' => $parts[0],
				'name' => $name
			];
		}

		return $result;
	}

	/**
	 *
	 * @return array
	 */
	public function getExcludeNames()
	{
		return $this->excludeNames;
	}

	/**
	 *
	 * @return string
	 */
	abstract protected function getFileExtension();

	/**
	 * retruns info to a key
	 *
	 * @param string $key
	 * @return array
	 */
	protected function getKeyInformation($key)
	{
		$keyInformation = array_reverse(array_map('strrev', explode('.', strrev($key), 2)));

		if (count($keyInformation) !== 2)
		{
			return [
				'file' => '',
				'filePart' => '',
				'key' => $key,
				'fileKey' => ''
			];
		}

		return [
			'file' => $keyInformation[0] . '.' . $this->getFileExtension(),
			'filePart' => $keyInformation[0],
			'key' => $keyInformation[1],
			'fileKey' => $key
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * prepares the translation
	 *
	 * @param string $keyPrefix
	 * @param array $translations
	 */
	protected function prepare($keyPrefix, array $translations)
	{
		if (empty($keyPrefix) === true)
		{
			return $translations;
		}

		$result = [];
		foreach ($translations as $key => $content)
		{
			$result[$keyPrefix . '.' . $key] = $content;
		}

		return $result;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \PhraseApp\Synchronize\Files\ReaderInterface::read()
	 */
	public function read(Synchronize $synchronize)
	{
		foreach ($this->collectFiles() as $fileEntry)
		{
			$translations = $this->readFile($fileEntry['file'], $fileEntry['name']);
			ksort($translations, SORT_NATURAL);
			$synchronize->addTranslations($fileEntry['locale'], $translations);
		}

		return true;
	}

	/**
	 *
	 * @param string $file
	 * @param string $keyPrefix
	 * @return array
	 */
	abstract protected function readFile($file, $keyPrefix = '');

	/**
	 *
	 * @param array $excludeNames
	 * @return self
	 */
	protected function setExcludeNames(array $excludeNames)
	{
		$this->excludeNames = $excludeNames;

		return $this;
	}

	/**
	 *
	 * @param string $path
	 * @return self
	 */
	protected function setPath($path)
	{
		if (is_dir($path) === false)
		{
			throw new InvalidPath($path);
		}

		$this->path = rtrim(str_replace('\\', '/', $path), '/') . '/';

		return $this;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \PhraseApp\Synchronize\Files\Writer::write()
	 */
	public function write(Synchronize $synchronize)
	{
		$translations = $synchronize->getTranslations();
		foreach ($translations as $locale => $translationsLocale)
		{
			// convert to files
			$translationsByFile = [];
			foreach ($translationsLocale as $key => $content)
			{
				// split file and key into pices
				$keyParts = array_reverse(array_map('strrev', explode('.', strrev($key), 2)));
				if (count($keyParts) !== 2)
				{
					continue;
				}

				// this is our translation key
				$translationKey = $keyParts[1];

				// this is the translation file with path part
				$file = $this->getPath() . $locale . '/' . $keyParts[0] . '.' . $this->getFileExtension();

				// is file in map?
				if (array_key_exists($file, $translationsByFile) === false)
				{
					// create map
					$translationsByFile[$file] = [];
				}

				// store key and content in map
				$translationsByFile[$file][$translationKey] = $content;
			}

			// save to files
			foreach ($translationsByFile as $file => $translationsFile)
			{
				$path = dirname($file);
				// create path of file
				if (is_dir($path) === false && @mkdir($path, 0777, true) === false)
				{
					throw new FailureCreateLocalePath($path, $locale);
				}

				$this->writeFile($file, $translationsFile);
			}
		}

		return true;
	}

	/**
	 *
	 * @param string $file
	 * @param array $translations
	 * @return self
	 */
	abstract protected function writeFile($file, array $translations);
}
