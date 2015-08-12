<?php
namespace DasRedTest\PhraseApp\Request;

use DasRed\PhraseApp\Config;
use DasRed\PhraseApp\Request\Translations;
use DasRed\PhraseApp\Request\Locales;
use DasRed\PhraseApp\Request\Keys;

/**
 *
 * @coversDefaultClass \DasRed\PhraseApp\Request\TranslationsAwareTrait
 */
class TranslationsAwareTraitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers ::getPhraseAppTranslations
	 * @covers ::setPhraseAppTranslations
	 */
	public function testGetSetPhraseAppTranslations()
	{
		$config = new Config('pp', 'a', 'de');
		$locales = new Locales($config);
		$keys = new Keys($config);

		$instanceOther = new Translations($config);

		$trait = $this->getMockBuilder('\DasRed\PhraseApp\Request\TranslationsAwareTrait')->setMethods(['getConfig', 'getPhraseAppKeys', 'getPhraseAppLocales'])->getMockForTrait();
		$trait->expects($this->any())->method('getConfig')->willReturn($config);
		$trait->expects($this->any())->method('getPhraseAppKeys')->willReturn($keys);
		$trait->expects($this->any())->method('getPhraseAppLocales')->willReturn($locales);

		$instance = $trait->getPhraseAppTranslations();
		$this->assertInstanceOf(Translations::class, $instance);
		$this->assertSame($instance, $trait->getPhraseAppTranslations());
		$this->assertSame($config, $instance->getConfig());
		$this->assertSame($keys, $instance->getPhraseAppKeys());
		$this->assertSame($locales, $instance->getPhraseAppLocales());

		$this->assertSame($trait, $trait->setPhraseAppTranslations($instanceOther));
		$this->assertSame($instanceOther, $trait->getPhraseAppTranslations());
	}
}
