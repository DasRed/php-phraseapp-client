<?php
namespace DasRed\PhraseApp\Collection;

class SubArray extends \ArrayIterator
{

	/**
	 *
	 * @var string
	 */
	protected $idKey;

	/**
	 *
	 * @param array $array
	 */
	public function __construct($idKey, $array = [])
	{
		$this->setIdKey($idKey);

		parent::__construct([]);

		$this->combine($array);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayIterator::append()
	 * @param array $entry
	 */
	public function append($entry)
	{
		$this->offsetSet($entry[$this->getIdKey()], $entry);
	}

	/**
	 *
	 * @param array $array
	 * @return self
	 */
	public function combine(array $array)
	{
		foreach ($array as $entry)
		{
			$this->append($entry);
		}

		return $this;
	}

	/**
	 *
	 * @param \Closure $callback
	 * @return self
	 */
	public function each(\Closure $callback)
	{
		foreach ($this as $index => $entry)
		{
			$callback($entry, $index);
		}

		return $this;
	}
	/**
	 *
	 * @param \Closure $callback
	 * @return array|null
	 */
	public function find(\Closure $callback)
	{
		foreach ($this as $index => $entry)
		{
			if ($callback($entry, $index) === true)
			{
				return $entry;
			}
		}

		return null;
	}

	/**
	 *
	 * @param string $id
	 * @return array
	 */
	public function get($id)
	{
		if ($this->offsetExists($id) !== true)
		{
			return null;
		}

		return $this->offsetGet($id);
	}

	/**
	 * @return string
	 */
	public function getIdKey()
	{
		return $this->idKey;
	}

	/**
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->getArrayCopy());
	}

	/**
	 *
	 * @param \Closure $callback
	 * @return array
	 */
	public function map(\Closure $callback)
	{
		$result = [];
		foreach ($this as $index => $entry)
		{
			$result[$index] = $callback($entry, $index);
		}

		return $result;
	}

	/**
	 *
	 * @param string $id
	 * @return self
	 */
	public function remove($id)
	{
		if ($this->offsetExists($id) !== true)
		{
			return $this;
		}

		$this->offsetUnset($id);

		return $this;
	}

	/**
	 *
	 * @param string $idKey
	 * @return self
	 */
	protected function setIdKey($idKey)
	{
		$this->idKey = $idKey;

		return $this;
	}
}