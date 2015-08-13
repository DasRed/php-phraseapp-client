<?php
namespace DasRed\PhraseApp\Command\Executor;

use DasRed\PhraseApp\Command\ExecutorAbstract;
use DasRed\PhraseApp\Request\LocalesAwareInterface;
use DasRed\PhraseApp\Request\LocalesAwareTrait;

abstract class LocaleAbstract extends ExecutorAbstract implements LocalesAwareInterface
{
	use LocalesAwareTrait;
}