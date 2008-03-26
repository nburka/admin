<?php

require_once 'Admin/AdminUI.php';
require_once 'Admin/pages/AdminIndex.php';
require_once 'Admin/exceptions/AdminNotFoundException.php';
require_once 'SwatDB/SwatDB.php';
require_once 'Admin/dataobjects/AdminUser.php';
require_once 'Swat/SwatTableStore.php';

/**
 * Details page for AdminUsers component
 *
 * @package   Admin
 * @copyright 2005-2007 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class AdminAdminUserDetails extends AdminIndex
{
	// {{{ private properties

	private $id;

	// }}}

	// init phase
	// {{{ protected function initInternal()

	protected function initInternal()
	{
		$this->ui->loadFromXML(dirname(__FILE__).'/details.xml');

		$this->id = intval(SiteApplication::initVar('id'));

		$this->initUser();

		// set a default order on the table view
		$index_view = $this->ui->getWidget('index_view');
		$index_view->setDefaultOrderbyColumn(
			$index_view->getColumn('login_date'));

		$this->navbar->createEntry(Admin::_('Details'));
	}

	// }}}
	// {{{ protected function initUser()
	protected function initUser()
	{
		$class_name = SwatDBClassMap::get('AdminUser');
		$this->user = new $class_name();
		$this->user->setDatabase($this->app->db);

		if (!$this->user->load($this->id)) {
			throw new AdminNotFoundException(
				sprintf(Admin::_('User with id "%s" not found.'),
					$this->id));
		}
	}

	// }}}

	// build phase
	// {{{ protected function buildInternal()

	protected function buildInternal()
	{
		parent::buildInternal();

		$frame = $this->ui->getWidget('index_frame');
		$frame->subtitle = sprintf($this->user->name);

		// rebuild the navbar
		$this->navbar->popEntry();
		$this->navbar->createEntry('Login History', 'AdminUser/LoginHistory');
		$this->navbar->createEntry($this->user->name);

		// set default time zone
		$date_column =
			$this->ui->getWidget('index_view')->getColumn('login_date');

		$date_renderer = $date_column->getRendererByPosition();
		$date_renderer->display_time_zone = $this->app->default_time_zone;
	}

	// }}}
	// {{{ protected function getTableModel()

	protected function getTableModel(SwatView $view)
	{
		return $this->user->history;
	}

	// }}}
}
?>