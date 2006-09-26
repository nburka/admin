<?php

require_once 'Admin/AdminApplication.php';
require_once 'Admin/exceptions/AdminNotFoundException.php';
require_once 'Site/SiteObject.php';

/**
 * Page request
 *
 * @package   Admin
 * @copyright 2004-2006 silverorange
 */
class AdminPageRequest extends SiteObject
{
	// {{{ protected properties

	protected $source;
	protected $component;
	protected $subcomponent;
	protected $title;
	protected $app;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new page request and resolves the component for the request
	 *
	 * @param AdminApplication $app the admin application creating the page
	 *                               request.
	 * @param string $source the source of the page request.
	 */
	public function __construct(AdminApplication $app, $source)
	{
		$this->source = $source;
		$this->app = $app;

		if (strlen($this->source) === 0)
			$this->source = $this->app->getFrontSource();

		if ($this->app->session->isLoggedIn()) {
			$source_exp = explode('/', $this->source);

			if (count($source_exp) == 1) {
				$this->component = $this->source;
				$this->subcomponent = $this->app->getDefaultSubComponent();
			} elseif (count($source_exp) == 2) {
				list($this->component, $this->subcomponent) = $source_exp;
			} else {
				throw new AdminNotFoundException(sprintf(Admin::_(
					"Invalid source '%s'."),
					$this->source));
			}

			if ($this->component == 'AdminSite') {
				$admin_titles = array(
					'Profile'        => Admin::_('Edit User Profile'),
					'Logout'         => Admin::_('Logout'),
					'Login'          => Admin::_('Login'),
					'Exception'      => Admin::_('Exception'),
					'Front'          => Admin::_('Index'),
					'MenuViewServer' => Admin::_(''),
				);

				if (isset($admin_titles[$this->subcomponent]))
					$this->title = $admin_titles[$this->subcomponent];
				else
					throw new AdminNotFoundException(sprintf(Admin::_(
						"Component not found for source '%s'."),
						$this->source));

			} else {

				$row = $this->app->queryForPage($this->component);

				if ($row === null)
					throw new AdminNotFoundException(sprintf(Admin::_(
						"Component not found for source '%s'."),
						$this->source));
				else
					$this->title = $row->component_title;
			}

		} else {
			$this->component = 'AdminSite';
			$this->subcomponent = 'Login';
			$this->title = Admin::_('Login');
		}
	}

	// }}}
	// {{{ public function getFilename()

	/**
	 * Finds the PHP file containing the class definition of the current
	 * sub-component
	 */
	public function getFilename()
	{
		$classfile = $this->component.'/'.$this->subcomponent.'.php';
		$file = null;

		if (file_exists('../../include/admin/components/'.$classfile)) {
			$file = '../../include/admin/components/'.$classfile;
		} else {
			$paths = explode(':', ini_get('include_path'));

			foreach ($paths as $path) {
				if (file_exists($path.'/Admin/components/'.$classfile)) {
					$file = 'Admin/components/'.$classfile;
					break;
				}
			}
		}
		
		return $file;
	}

	// }}}
	// {{{ public function getClassname()

	public function getClassname()
	{
		return $this->component.$this->subcomponent;
	}

	// }}}
	// {{{ public function getTitle()

	public function getTitle()
	{
		return $this->title;
	}
	
	// }}}
	// {{{ public function getComponent()

	public function getComponent()
	{
		return $this->component;
	}
	
	// }}}
	// {{{ public function getSubComponent()

	public function getSubComponent()
	{
		return $this->subcomponent;
	}
	
	// }}}
	// {{{ public function getSource()

	public function getSource()
	{
		return $this->source;
	}
	
	// }}}
}

?>
