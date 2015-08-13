<?php
namespace DasRed\PhraseApp\Command\Executor;

use DasRed\PhraseApp\Command\ExecutorAbstract;
use DasRed\PhraseApp\Request\KeysAwareInterface;
use DasRed\PhraseApp\Request\KeysAwareTrait;

abstract class KeyAbstract extends ExecutorAbstract implements KeysAwareInterface
{
	use KeysAwareTrait;
}