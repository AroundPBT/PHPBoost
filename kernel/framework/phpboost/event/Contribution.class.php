<?php
/*##################################################
 *                          Contribution.class.php
 *                            -------------------
 *   begin                : July 21, 2008
 *   copyright            : (C) 2008 Beno�t Sautel
 *   email                : ben.popeye@phpboost.com
 *
 *
 ###################################################
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/


## Constants ##

//Bit on which are checked the authorizations

/**
 * @package {@package}
 * @author Beno�t Sautel <ben.popeye@phpboost.com>
 * @desc This class represents a contribution made by a user to complete the content of the website.
 * All the contributions are managed in the contribution panel.
 */
class Contribution extends Event
{
	const CONTRIBUTION_AUTH_BIT = 1;

	/**
	 * @var string Description of the contribution (for instance to justify a contribution).
	 */
	private $description;

	/**
	 * @var string String containing the identifier of the module corresponding to the contribution (ex: forum).
	 */
	private $module = '';

	/**
	 * @var Date Date at which the contribution has been processed (if it is obviously). Default value: date at which is created the contribution.
	 */
	private $fixing_date;

	/**
	 * @var array Authorization array containing the people who can treat the contribution.
	 */
	private $auth = array();

	/**
	 * @var int Identifier of the member who has posted the contribution.
	 */
	private $poster_id = 0;

	/**
	 * @var int Identifier of the member who has fixed the contribution.
	 */
	private $fixer_id = 0;

	/**
	 * @var string Login of the member who has posted the contribution.
	 */
	private $poster_login = '';

	/**
	 * @var string Login of the member who has fixed the contribution.
	 */
	private $fixer_login = '';
	
	private $sql_querier;


	/**
	 * @desc Builds a Contribution object.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->current_status = Event::EVENT_STATUS_UNREAD;
		$this->creation_date = new Date();
		$this->fixing_date = new Date();
		$this->module = Environment::get_running_module_name();
		$this->sql_querier = PersistenceContext::get_sql();
	}

	/**
	 * @desc Builds a contribution object from its whole parameters.
	 * @param int $id Contribution id.
	 * @param string $entitled Contribution entitled.
	 * @param string $fixing_url URL associated to the event.
	 * @param string $module Module identifier at which the contribution is attached.
	 * @param int status Contribution status.
	 * @param Date $creation_date Contribution creation date.
	 * @param Date $fixing_date Contribution fixing date.
	 * @param mixed[] $auth Auth array determining the people who can treat the contribution.
	 * @param int $poster_id Contribution creator id.
	 * @param int $fixer_id Contribution fixer id.
	 * @param int $id_in_module Id of the element associated to the contribution.
	 * @param string $identifier Contribution identifier.
	 * @param string $type Contribution type.
	 * @param string $poster_login Login of the poster of the contribution.
	 * @param string $fixer_login Login of the fixer of the contribution.
	 */
	public function build($id, $entitled, $description, $fixing_url, $module, $status, $creation_date, $fixing_date, $auth, $poster_id, $fixer_id, $id_in_module, $identifier, $type, $poster_login = '', $fixer_login = '')
	{
		//Building parent class
		parent::build_event($id, $entitled, $fixing_url, $status, $creation_date, $id_in_module, $identifier, $type);

		//Setting its whole parameters
		$this->description 	= $description;
		$this->module 		= $module;
		$this->fixing_date 	= $fixing_date;
		$this->auth 		= $auth;
		$this->poster_id 	= $poster_id;
		$this->fixer_id 	= $fixer_id;
		$this->poster_login = $poster_login;
		$this->fixer_login 	= $fixer_login;

		//Setting the modification flag to false, it just comes to be loaded
		$this->must_regenerate_cache = false;
	}

	/**
	 * @desc Sets the module in which the contribution is used.
	 * @param string $module Module identifier (for example the name of the module folder).
	 */
	public function set_module($module)
	{
		$this->module = $module;
	}

	/**
	 * @desc Sets the fixing date.
	 * @param Date $date Date
	 */
	public function set_fixing_date($date)
	{
		if (is_object($date) && $date instanceof Date)
		{
			$this->fixing_date = $date;
		}
	}

	/**
	 * @desc Set the status of the contribution.
	 * @param int $new_current_status One of those elements:
	 * <ul>
	 * 	<li>Event::EVENT_STATUS_UNREAD if it's not read.</li>
	 * 	<li>Event::EVENT_STATUS_BEING_PROCESSED if the event is beeing processed</li>
	 * 	<li>Event::EVENT_STATUS_PROCESSED if the event is processed.
	 * </ul>
	 */
	public function set_status($new_current_status)
	{
		if (in_array($new_current_status, array(Event::EVENT_STATUS_UNREAD, Event::EVENT_STATUS_BEING_PROCESSED, Event::EVENT_STATUS_PROCESSED), TRUE))
		{
			//If it just comes to be processed, we automatically consider it as processed
			if ($this->current_status != Event::EVENT_STATUS_PROCESSED && $new_current_status == Event::EVENT_STATUS_PROCESSED)
			{
				$this->fixing_date = new Date();
				//If the fixer id is not defined, we define it
				if ($this->fixer_id == 0)
				{
					$this->fixer_id = AppContext::get_current_user()->get_attribute('user_id');
				}
			}

			$this->current_status = $new_current_status;
		}
		//Default
		else
		{
			$this->current_status = Event::EVENT_STATUS_UNREAD;
		}

		$this->must_regenerate_cache = true;
	}

	/**
	 * @desc Sets the authorization of the contribution. It will determine who will be able to treat the contribution.
	 * @param mixed[] $auth Auth array.
	 */
	public function set_auth($auth)
	{
		if (is_array($auth))
		{
			$this->auth = $auth;
		}
	}

	/**
	 * @desc Sets the id of the poster.
	 * @param int $poster_id Id.
	 */
	public function set_poster_id($poster_id)
	{
		if ($poster_id  > 0)
		{
			$this->poster_id = $poster_id;
			//Assigning also the associated login
			$this->poster_login = $this->sql_querier->query("SELECT login FROM " . DB_TABLE_MEMBER . " WHERE user_id = '" . $poster_id . "'", __LINE__, __FILE__);
		}
	}

	/**
	 * @desc Sets the id of the fixer.
	 * @param int $fixer_id Id.
	 */
	public function set_fixer_id($fixer_id)
	{
		if ($fixer_id  > 0)
		{
			$this->fixer_id = $fixer_id;
			//Assigning also the associated login
			$this->fixer_login = $this->sql_querier->query("SELECT login FROM " . DB_TABLE_MEMBER . " WHERE user_id = '" . $fixer_id . "'", __LINE__, __FILE__);
		}
	}

	/**
	 * @desc Sets the description of the contribution.
	 * @param string $description Description (can be some HTML content).
	 */
	public function set_description($description)
	{
		if (is_string($description))
		{
			$this->description = $description;
		}
	}

	/**
	 * @desc Gets the description of the contribution.
	 * @return string the description
	 */
	public function get_description()
	{
		return $this->description;
	}

	/**
	 * @desc Gets the module in which the contribution is used.
	 * @return string The module identifier (for example the name of its folder).
	 */
	public function get_module()
	{
		return $this->module;
	}

	/**
	 * @desc Gets the contribution fixing date.
	 * @return The date at which the contribution has been treated.
	 */
	public function get_fixing_date()
	{
		return $this->fixing_date;
	}

	/**
	 * @desc Gets the authorization of treatment of this contribution.
	 * @return mixed[] The authorization array.
	 */
	public function get_auth()
	{
		return $this->auth;
	}

	/**
	 * @desc Gets the identifier of the poster.
	 * @return int Its id.
	 */
	public function get_poster_id()
	{
		return $this->poster_id;
	}

	/**
	 * @desc Gets the identifier of the fixer.
	 * @return int Its id.
	 */
	public function get_fixer_id()
	{
		return $this->fixer_id;
	}

	/**
	 * @desc Gets the poster login.
	 * @return string The poster login.
	 */
	public function get_poster_login()
	{
		return $this->poster_login;
	}

	/**
	 * @desc Gets the fixer login.
	 * @return string The fixer login.
	 */
	public function get_fixer_login()
	{
		return $this->fixer_login;
	}

	/**
	 * @desc Gets the contribution status name. It's automatically translated in the user language, ready to be displayed.
	 * @return string The status name.
	 */
	public function get_status_name()
	{
		switch ($this->current_status)
		{
			case Event::EVENT_STATUS_UNREAD:
				return LangLoader::get_message('contribution_status_unread', 'main');
			case Event::EVENT_STATUS_BEING_PROCESSED:
				return LangLoader::get_message('contribution_status_being_processed', 'main');
			case Event::EVENT_STATUS_PROCESSED:
				return LangLoader::get_message('contribution_status_processed', 'main');
		}
	}

	/**
	 * @desc Gets the name of the module in which the contribution is used.
	 * @return string The module name.
	 */
	public function get_module_name()
	{
		if (!empty($this->module))
		{
			$module = ModulesManager::get_module($this->module);
			
			return $module ? $module->get_configuration()->get_name() : '';
		}
		else
		{
			return '';
		}
	}
}
?>