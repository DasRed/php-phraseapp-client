<?php
namespace DasRed\PhraseApp\Request;

use DasRed\PhraseApp\Request;
use DasRed\PhraseApp\Exception as BaseException;
use DasRed\PhraseApp\Collection\SubArrayAwareTrait;

/**
 *
 * @see http://docs.phraseapp.com/api/v2/keys/
 */
class Keys extends Request
{
	use SubArrayAwareTrait;
	use LoadTrait;
	const URL_API = 'projects/:project_id/keys/';

	/**
	 *
	 * @param string $key
	 * @param string $tag
	 * @return boolean
	 */
	public function addTag($key, $tag)
	{
		// find the key
		$translationKey = $this->getCollection()->get($key);

		// key not found... create the key
		if ($translationKey === null)
		{
			return $this->create($key, '', [$tag]);
		}

		// key found update the key
		return $this->update($key, $translationKey['name'], $translationKey['description'], array_merge($translationKey['tags'], [
			$tag
		]));
	}

	/**
	 * creates a key
	 *
	 * @param string $name
	 * @param string $description
	 * @param array $tags
	 * @return boolean
	 * @see http://docs.phraseapp.com/api/v2/keys/#create
	 */
	public function create($name, $description = '', array $tags = [])
	{
		try
		{
			$response = $this->methodPost(self::URL_API, [
				'name' => $name,
				'description' => $description,
				'tags' => implode(',', $tags)
			]);

			$this->getCollection()->append($response);
		}
		catch (BaseException $exception)
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param string|array $key
	 * @return boolean
	 * @see http://docs.phraseapp.com/api/v2/keys/#destroy
	 */
	public function delete($key)
	{
		if (is_array($key) === true)
		{
			return $this->deleteMany($key);
		}

		$translationKey = $this->getCollection()->get($key);
		if ($translationKey === null)
		{
			return true;
		}

		try
		{
			$this->methodDelete(self::URL_API . $translationKey['id']);

			$this->getCollection()->remove($key);
		}
		catch (BaseException $exception)
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param array $keys
	 * @return boolean
	 * @see http://docs.phraseapp.com/api/v2/keys/#destroy
	 */
	protected function deleteMany(array $keys)
	{
		foreach ($keys as $key)
		{
			if ($this->delete($key) === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * fetch all translation keys
	 *
	 * @return array
	 * @see http://docs.phraseapp.com/api/v2/keys/#index
	 */
	public function fetch()
	{
		return $this->getCollection()->keys();
	}

	/**
	 * @return string
	 */
	protected function getIdKey()
	{
		return 'name';
	}

	/**
	 * @return string
	 */
	protected function getUrlApi()
	{
		return self::URL_API;
	}

	/**
	 * update a key
	 *
	 * @param string $key
	 * @param string $name
	 * @param string $description
	 * @param array $tags
	 * @return boolean
	 * @see http://docs.phraseapp.com/api/v2/keys/#update
	 */
	public function update($key, $name, $description = null, array $tags = null)
	{
		$translationKey = $this->getCollection()->get($key);

		$translationKey['name'] = $name;

		if ($description !== null)
		{
			$translationKey['description'] = $description;
		}

		if ($tags !== null)
		{
			$translationKey['tags'] = $tags;
		}

		if (array_key_exists('tags', $translationKey) === true && is_array($translationKey['tags']) === true)
		{
			$translationKey['tags'] = implode(',', $translationKey['tags']);
		}

		try
		{
			$response = $this->methodPatch(self::URL_API . $translationKey['id'], $translationKey);

			// overwrite the update in collection
			$this->getCollection()->append($response);
		}
		catch (BaseException $exception)
		{
			return false;
		}

		return true;
	}
}
