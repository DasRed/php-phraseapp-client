<?php
namespace DasRed\PhraseApp\Synchronize\Files\Type;

use DasRed\PhraseApp\Synchronize\Files\TypeAbstract;

class Php extends TypeAbstract
{

	/**
	 *
	 * @param string $key
	 * @return string
	 */
	public function getDescriptionForKey($key)
	{
		$keyInformation = $this->getKeyInformation($key);

		return 'This is a translation key from the file "' . $keyInformation['file'] . '".';
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \PhraseApp\Synchronize\Files\TypeAbstract::getFileExtension()
	 */
	protected function getFileExtension()
	{
		return 'php';
	}

	/**
	 *
	 * @param string $key
	 * @return array
	 */
	public function getTagsForKey($key)
	{
		$keyInformation = $this->getKeyInformation($key);

		return [
			$keyInformation['filePart']
		];
	}

	/**
	 *
	 * @param string $file
	 * @param string $keyPrefix
	 * @return array
	 */
	protected function readFile($file, $keyPrefix = '')
	{
		return $this->prepare($keyPrefix, require $file);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \PhraseApp\Synchronize\Files\TypeAbstract::writeFile()
	 */
	protected function writeFile($file, array $translations)
	{
		ksort($translations, SORT_NATURAL);

		$fHandle = fopen($file, 'w');
		fwrite($fHandle, "<?php\n");
		fwrite($fHandle, "\n");
		fwrite($fHandle, "return [\n");

		foreach ($translations as $key => $entry)
		{
			fwrite($fHandle, sprintf('	\'%s\' => \'%s\',', $key, addcslashes($entry, '\'')) . "\n");
		}

		fwrite($fHandle, "];\n");
		fclose($fHandle);

		return $this;
	}
}
