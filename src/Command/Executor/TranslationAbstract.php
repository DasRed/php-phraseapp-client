<?php
namespace DasRed\PhraseApp\Command\Executor;

use DasRed\PhraseApp\Command\ExecutorAbstract;
use DasRed\PhraseApp\Request\TranslationsAwareInterface;
use DasRed\PhraseApp\Request\TranslationsAwareTrait;
use DasRed\PhraseApp\Request\KeysAwareInterface;
use DasRed\PhraseApp\Request\LocalesAwareInterface;
use DasRed\PhraseApp\Request\KeysAwareTrait;
use DasRed\PhraseApp\Request\LocalesAwareTrait;

abstract class TranslationAbstract extends ExecutorAbstract implements TranslationsAwareInterface, KeysAwareInterface, LocalesAwareInterface
{
	use TranslationsAwareTrait;
	use KeysAwareTrait;
	use LocalesAwareTrait;
}