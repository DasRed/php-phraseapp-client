<?php
namespace DasRed\PhraseApp\Synchronize;

use DasRed\PhraseApp\Synchronize;
use DasRed\PhraseApp\Synchronize\Files\HandlerInterface;
use DasRed\PhraseApp\Synchronize\Exception\FailureAddKey;

class Files extends Synchronize
{

	/**
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 *
	 * @param HandlerInterface $handler
	 * @return self
	 */
	public function appendHandler(HandlerInterface $handler)
	{
		$this->handlers[] = $handler;

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	protected function read()
	{
		$this->log('Loading translations');

		/* @var $handler HandlerInterface */
		foreach ($this->handlers as $handler)
		{
			$handler->read($this);
		}

		return $this;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \PhraseApp\Synchronize::synchronize()
	 */
	public function synchronize()
	{
		$this->read();

		if (parent::synchronize() === false)
		{
			return false;
		}

		$this->write();

		return true;
	}

	/**
	 *
	 * @param string $key
	 * @return self
	 * @throws FailureAddKey
	 */
	protected function synchronizeKeysCreateKey($key)
	{
		$description = '';
		$tags = [];

		/* @var $handler HandlerInterface */
		foreach ($this->handlers as $handler)
		{
			$descriptionFromHandler = $handler->getDescriptionForKey($key);
			if ($descriptionFromHandler !== '')
			{
				$description .= $descriptionFromHandler;
			}

			$tags = array_merge($tags, $handler->getTagsForKey($key));
		}

		$tags = array_unique($tags, SORT_NATURAL);
		sort($tags, SORT_NATURAL);

		if ($this->getPhraseAppKeys()->create($key, $description, $tags) === false)
		{
			throw new FailureAddKey($key);
		}

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	protected function write()
	{
		$this->log('Saving translations');

		/* @var $handler HandlerInterface */
		foreach ($this->handlers as $handler)
		{
			$handler->write($this);
		}

		return $this;
	}
}
