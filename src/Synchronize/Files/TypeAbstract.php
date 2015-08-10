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
	protected $path = null;

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
			$filePart = trim(str_replace([
				'\\',
				$this->getPath()
			], [
				'/',
				''
			], $file[0]), '/');
			$parts = explode('/', $filePart, 2);

			// key name
			$name = (strpos($parts[1], '/') !== false ? dirname($parts[1]) . '/' : '') . basename($parts[1], '.' . $this->getFileExtension());
			if (in_array($name, $this->getExcludeNames()) === true || in_array(basename($name), $this->getExcludeNames()) === true)
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

		$this->path = trim(str_replace('\\', '/', $path), '/') . '/';

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
				$keyParts = array_reverse(array_map('strrev', explode('.', strrev($key), 2)));
				if (count($keyParts) !== 2)
				{
					continue;
				}

				$fileName = $keyParts[0];
				$translationKey = $keyParts[1];

				if (array_key_exists($fileName, $translationsByFile) === false)
				{
					$translationsByFile[$fileName] = [];
				}

				$translationsByFile[$fileName][$translationKey] = $content;
			}

			$pathToWrite = $this->getPath() . $locale;
			if (strpos($fileName, '/') !== false)
			{
				$pathToWrite .= '/' . basename($fileName);
			}
			if (is_dir($pathToWrite) === false && mkdir($pathToWrite, 0777, true) === false)
			{
				throw new FailureCreateLocalePath($pathToWrite);
			}

			// save to files
			foreach ($translationsByFile as $fileName => $translationsFile)
			{
				$this->writeFile($pathToWrite . '/' . $fileName . '.' . $this->getFileExtension(), $translationsFile);
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
