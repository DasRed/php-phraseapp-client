<?php
namespace DasRed\PhraseApp\Collection;

trait SubArrayAwareTrait
{
	/**
	 *
	 * @var SubArray
	 */
	protected $collection;

	/**
	 * @return SubArray
	 */
	public function getCollection()
	{
		if ($this->collection === null)
		{
			$this->collection = new SubArray($this->getIdKey(), $this->load());
		}

		return $this->collection;
	}

	/**
	 * @return string
	 */
	abstract protected function getIdKey();

	/**
	 * @return array
	 */
	abstract protected function load();
}