<?php
/**
 * Part of the Sentry package for Fuel.
 *
 * @package    Sentry
 * @version    1.0
 * @author     Cartalyst LLC
 * @license    MIT License
 * @copyright  2011 Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Sentry;

use Config;
use DB;
use FuelException;

class SentryGroupException extends \FuelException {}
class SentryGroupNotFoundException extends SentryGroupException {}

/**
 * Handles all of the Sentry group logic.
 *
 * @author Dan Horrigan
 */
class Sentry_Group
{

	protected static $table = '';
	protected static $join_table = '';

	/**
	 * Gets the table names
	 */
	public static function _init()
	{
		static::$table = strtolower(Config::get('sentry.table.groups'));
		static::$join_table = strtolower(Config::get('sentry.table.users_groups'));
	}

	protected $group = array();

	/**
	 * Gets all the group info.
	 *
	 * @param   string|int  Group id or name
	 * @return  void
	 */
	public function __construct($id = null)
	{
		if ($id === null)
		{
			return;
		}

		if (is_numeric($id))
		{
			if ($id <= 0)
			{
				throw new \SentryGroupException(__('sentry.invalid_group_id'));
			}
			$field = 'id';
		}
		else
		{
			$field = 'name';
		}

		$group = DB::select()
		          ->from(static::$table)
		          ->where($field, $id)
		          ->execute();

		// if there was a result - update user
		if (count($group))
		{
			$this->group = $group->current();
		}
		// group doesn't exist
		else
		{
			throw new \SentryGroupNotFoundException(__('sentry.group_not_found', array('group' => $id)));
		}
	}

	/**
	 * Creates the given group.
	 *
	 * @param   array  Group info
	 * @return  int|bool
	 */
	public function create($group)
	{
		if ( ! array_key_exists('name', $group))
		{
			throw new \SentryGroupException(__('sentry.group_name_empty'));
		}

		if (Sentry::group_exists($group['name']))
		{
			throw new \SentryGroupException(__('sentry.group_already_exists', array('group' => 'name')));
		}

		if ( ! array_key_exists('level', $group))
		{
			throw new \SentryGroupException(__('sentry.group_level_empty'));
		}

		if ( ! array_key_exists('is_admin', $group))
		{
			$group['is_admin'] = 0;
		}

		list($insert_id, $rows_affected) = DB::insert(static::$table)->set($group)->execute();

		return ($rows_affected > 0) ? $insert_id : false;
	}

	/**
	 * Checks if the Field is set or not.
	 *
	 * @param   string  Field name
	 * @return  bool
	 */
	public function __isset($field)
	{
		return array_key_exists($field, $this->group);
	}

	/**
	 * Gets a field value of the group
	 *
	 * @param   string  Field name
	 * @return  mixed
	 * @throws  SentryGroupException
	 */
	public function __get($field)
	{
		return $this->get($field);
	}

	/**
	 * Gets a given field (or array of fields).
	 *
	 * @param   string|array  Field(s) to get
	 * @return  mixed
	 * @throws  SentryGroupException
	 */
	public function get($field = null)
	{
		// make sure a group id is set
		if (empty($this->group['id']))
		{
			throw new \SentryGroupException(__('sentry.no_group_selected'));
		}

		// if no fields were passed - return entire user
		if ($field === null)
		{
			return $this->group;
		}
		// if field is an array - return requested fields
		else if (is_array($field))
		{
			$values = array();

			// loop through requested fields
			foreach ($field as $key)
			{
				// check to see if field exists in group
				if (array_key_exists($key, $this->group))
				{
					$values[$key] = $this->group[$key];
				}
				else
				{
					throw new \SentryGroupException(
						__('sentry.not_found_in_group_object', array('field' => $key))
					);
				}
			}

			return $values;
		}
		// if single field was passed - return its value
		else
		{
			// check to see if field exists in group
			if (array_key_exists($field, $this->group))
			{
				return $this->group[$field];
			}

			throw new \SentryGroupException(
				__('sentry.not_found_in_group_object', array('field' => $field))
			);
		}
	}

	/**
	 * Gets all the users for this group.
	 *
	 * @return  array
	 */
	public function users()
	{
		$users_table = Config::get('sentry.table.users');
		$groups_table = Config::get('sentry.table.groups');

		$users = DB::select($users_table.'.*')
			->from($users_table)
			->where(static::$join_table.'.group_id', '=', $this->group['id'])
			->join(static::$join_table)
			->on(static::$join_table.'.group_id', '=', $users_table.'.id')
			->execute()->as_array();

		if ( ! empty($users))
		{
			return array();
		}

		// Unset password stuff
		foreach ($users as & $user)
		{
			unset($user['password']);
			unset($user['password_reset_hash']);
			unset($user['temp_password']);
			unset($user['remember_me']);
		}

		return $users;
	}

	/**
	 * Returns all groups
	 *
	 * @return  array
	 */
	public function all()
	{
		return DB::select()->from(static::$table)->execute()->as_array();
	}

}
