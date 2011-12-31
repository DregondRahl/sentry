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

namespace Fuel\Migrations;

class Install_Sentry_Auth
{

	public function up()
	{
		\Config::load('sentry', true);

		\DBUtil::create_table(\Config::get('sentry.table.users'), array(
			'id'					=> array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),
			'username'				=> array('constraint' => 50, 'type' => 'varchar'),
			'email'					=> array('constraint' => 120, 'type' => 'varchar'),
			'password'				=> array('constraint' => 81, 'type' => 'varchar'),
			'password_reset_hash'	=> array('constraint' => 81, 'type' => 'varchar'),
			'temp_password'			=> array('constraint' => 81, 'type' => 'varchar'),
			'remember_me'			=> array('constraint' => 81, 'type' => 'varchar'),
			'activation_hash'		=> array('constraint' => 81, 'type' => 'varchar'),
			'ip_address'			=> array('constraint' => 40, 'type' => 'varchar'),
			'last_login'			=> array('constraint' => 10, 'type' => 'int'),
			'updated_at'			=> array('constraint' => 10, 'type' => 'int'),
			'created_at'			=> array('constraint' => 10, 'type' => 'int'),
			'status'				=> array('constraint' => 3, 'type' => 'tinyint'),
			'activated'				=> array('contsraint' => 3, 'type' => 'tinyint'),
		), array('id'));

		\DBUtil::create_table(\Config::get('sentry.table.users_metadata'), array(
			'user_id'		=> array('constraint' => 11, 'type' => 'int'),
			'first_name'	=> array('constraint' => 50, 'type' => 'varchar'),
			'last_name'		=> array('constraint' => 50, 'type' => 'varchar'),
		), array('user_id'));

		\DBUtil::create_table(\Config::get('sentry.table.groups'), array(
			'id'		=> array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),
			'name'		=> array('constraint' => 200, 'type' => 'varchar'),
			'level'		=> array('constraint' => 10, 'type' => 'int'),
			'is_admin'	=> array('constraint' => 3, 'type' => 'tinyint'),
		), array('id'));

		\DBUtil::create_table(\Config::get('sentry.table.users_suspended'), array(
			'id'				=> array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),
			'login_id'			=> array('constraint' => 120, 'type' => 'varchar'),
			'attempts'			=> array('constraint' => 50, 'type' => 'int'),
			'ip'				=> array('constraint' => 40, 'type' => 'varchar'),
			'last_attempt_at'	=> array('constraint' => 10, 'type' => 'int'),
			'suspended_at'		=> array('constraint' => 10, 'type' => 'int'),
			'unsuspend_at'		=> array('constraint' => 10, 'type' => 'int'),
		), array('id'));

		\DBUtil::create_table(\Config::get('sentry.table.users_groups'), array(
			'user_id'	=> array('constraint' => 11, 'type' => 'int'),
			'group_id'	=> array('constraint' => 11, 'type' => 'int'),
		));
		
		\DBUtil::create_index(\Config::get('sentry.table.users_metadata'), 'user_id');
		\DBUtil::create_index(\Config::get('sentry.table.users_groups'), array('user_id', 'group_id'), 'user_group');
	}

	public function down()
	{
		\Config::load('sentry', true);

		\DBUtil::drop_table(\Config::get('sentry.table.users'));
		\DBUtil::drop_table(\Config::get('sentry.table.groups'));
		\DBUtil::drop_table(\Config::get('sentry.table.users_metadata'));
		\DBUtil::drop_table(\Config::get('sentry.table.users_groups'));
		\DBUtil::drop_table(\Config::get('sentry.table.users_suspended'));
	}

}