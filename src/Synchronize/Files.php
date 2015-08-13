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

		parent::synchronize();

		$this->write();

		return $this;
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

		/* @var $handler HandlerInterface */
		foreach ($this->handlers as $handler)
		{
			$descriptionFromHandler = $handler->getDescriptionForKey($key);
			if ($descriptionFromHandler !== '')
			{
				$description .= $descriptionFromHandler;
			}
		}

		if ($this->getPhraseAppKeys()->create($key, $description) === false)
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
