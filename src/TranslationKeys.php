<?php
namespace DasRed\PhraseApp;

/**
 *
 * @see https://phraseapp.com/docs/api/translation_keys?language=en
 */
class TranslationKeys extends Sessions
{

	const URL_API = 'translation_keys/';

	/**
	 *
	 * @param string $key
	 * @param string $tag
	 * @return boolean
	 */
	public function addTag($key, $tag)
	{
		$translationKey = $this->get($key);

		if (array_key_exists('tag_list', $translationKey) === false || is_array($translationKey['tag_list']) === false)
		{
			$translationKey['tag_list'] = [];
		}

		$translationKey['tag_list'][] = $tag;
		array_unique($translationKey['tag_list']);
		$translationKey['tag_names'] = implode(',', $translationKey['tag_list']);
		unset($translationKey['tag_list']);

		try
		{
			$this->methodPatch(self::URL_API . '/' . $this->getId($key), [
				'auth_token' => $this->getSessionToken(),
				'translation_key' => $translationKey
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		return true;
	}

	/**
	 * creates a key
	 *
	 * @param string $name
	 * @param string $description
	 * @param array $tags
	 * @param string $dataType
	 * @return boolean
	 */
	public function create($name, $description = '', array $tags = array(), $dataType = 'string')
	{
		try
		{
			$this->methodPost(self::URL_API, [
				'auth_token' => $this->getSessionToken(),
				'translation_key' => [
					'name' => $name,
					'description' => $description,
					'data_type' => $dataType,
					'tag_names' => implode(',', $tags)
				]
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param string|array $key
	 * @return boolean
	 */
	public function delete($key)
	{
		if (is_array($key) === true)
		{
			return $this->deleteMany($key);
		}

		$id = $this->getId($key);
		if ($id === false)
		{
			return true;
		}

		try
		{
			$result = $this->methodDelete(self::URL_API . $id, [
				'auth_token' => $this->getSessionToken()
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		if ($result['success'] !== true)
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param array $keys
	 * @return boolean
	 */
	protected function deleteMany(array $keys)
	{
		$ids = $this->fetchIds($keys);
		if (count($ids) === 0)
		{
			return true;
		}

		try
		{
			$result = $this->methodDelete(self::URL_API . 'destroy_multiple', [
				'ids' => $ids,
				'auth_token' => $this->getSessionToken()
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		if ($result['success'] !== true)
		{
			return false;
		}

		return true;
	}

	/**
	 * fetch all translation keys
	 *
	 * @return array
	 */
	public function fetch()
	{
		try
		{
			$response = $this->methodGet(self::URL_API);
		}
		catch (Exception $exception)
		{
			return [];
		}

		return array_map(function (array $entry)
		{
			return $entry['name'];
		}, $response);
	}

	/**
	 *
	 * @param array $keys
	 * @return array
	 */
	protected function fetchIds(array $keys)
	{
		try
		{
			$response = $this->methodGet(self::URL_API, [
				'key_names' => $keys
			]);
		}
		catch (Exception $exception)
		{
			return [];
		}

		return array_map(function (array $entry)
		{
			return $entry['id'];
		}, $response);
	}

	/**
	 *
	 * @param string $key
	 * @return int
	 */
	protected function get($key)
	{
		try
		{
			$response = $this->methodGet(self::URL_API, [
				'key_names' => [
					$key
				]
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		if (count($response) === 0)
		{
			return false;
		}

		return $response[0];
	}

	/**
	 *
	 * @param string $key
	 * @return int
	 */
	protected function getId($key)
	{
		return $this->get($key)['id'];
	}

	/**
	 * update a key
	 *
	 * @param string $key
	 * @param string $name
	 * @param string $description
	 * @param array $tags
	 * @param string $dataType
	 * @return boolean
	 */
	public function update($key, $name = null, $description = null, array $tags = null, $dataType = null)
	{
		$translationKey = $this->get($key);

		if ($name !== null)
		{
			$translationKey['name'] = $name;
		}
		if ($description !== null)
		{
			$translationKey['description'] = $description;
		}
		if ($dataType !== null)
		{
			$translationKey['data_type'] = $dataType;
		}
		if ($tags !== null)
		{
			$translationKey['tag_list'] = $tags;
		}

		if (array_key_exists('tag_list', $translationKey) === true && is_array($translationKey['tag_list']) === true)
		{
			$translationKey['tag_names'] = implode(',', $translationKey['tag_list']);
			unset($translationKey['tag_list']);
		}

		try
		{
			$this->methodPatch(self::URL_API . $this->getId($key), [
				'auth_token' => $this->getSessionToken(),
				'translation_key' => $translationKey
			]);
		}
		catch (Exception $exception)
		{
			return false;
		}

		return true;
	}
}
