<?php

/**
 * File Doc Comment_
 * PHP version 5
 *
 * @category  Component
 * @package   Joomla.Administrator
 * @author    Joomla! <admin@joomla.org>
 * @copyright (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @link      admin@joomla.org
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * PlgSystemTour
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemTour extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $guide;
	/**
	 * function for getSubscribedEvents : new Joomla 4 feature
	 *
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeRender' => 'onBeforeRender',
			'onBeforeCompileHead' => 'onBeforeCompileHead'
		];
	}
	/**
	 * Listener for the `onBeforeRender` event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeRender()
	{
		// Run in backend
		if ($this->app->isClient('administrator'))
		{
			/**
			 * Booting of the Component to get the data in JSON Format
			 */
			$myTours = $this->app->bootComponent('com_guidedtours')->getMVCFactory()->createModel('Tours', 'Administrator', ['ignore_request' => true]);
			$mySteps = $this->app->bootComponent('com_guidedtours')->getMVCFactory()->createModel('Steps', 'Administrator', ['ignore_request' => true]);

			$tours = $myTours->getItems();
			$steps = $mySteps->getItems();
			$document = Factory::getDocument();

			$newsteps = [];

			foreach ($steps as $step)
			{
				if (!isset($newsteps[$step->tour_id]))
				{
					$newsteps[$step->tour_id] = [];
				}

				$newsteps[$step->tour_id][] = $step;
			}

			foreach ($tours as $tour)
			{
				$tour->steps = [];

				if (isset($newsteps[$tour->id]))
				{
					$tour->steps = $newsteps[$tour->id];
				}
			}

			$mySteps = json_encode($tours);

			$document->addScriptOptions('mySteps', $mySteps);

			$toolbar = Toolbar::getInstance('toolbar');
			$dropdown = $toolbar->dropdownButton()
				->text('Take the Tour')
				->toggleSplit(false)
				->icon('fas fa-car-side')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			foreach ($tours as $a)
			{
				$childBar->separatorButton($a->title)
					->text($a->title)
					->buttonClass('btn btn-primary ');
			}
		}
	}

	/**
	 * Listener for the `onBeforeCompileHead` event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{

		if ($this->app->isClient('administrator'))
		{
			HTMLHelper::_(
				'script',
				Uri::root() . 'build/media_source/plg_system_tour/js/guide.js',
				array('version' => 'auto', 'relative' => true)
			);

			HTMLHelper::_(
				'script',
				Uri::root() . 'build/media_source/plg_system_tour/js/shepherd.min.js',
				array('version' => 'auto', 'relative' => true)
			);
			HTMLHelper::_(
				'script',
				Uri::root() . 'build/media_source/plg_system_tour/js/popper.min.js',
				array('version' => 'auto', 'relative' => true)
			);
			HTMLHelper::_(
				'stylesheet',
				Uri::root() . 'build/media_source/plg_system_tour/css/shepherd.css',
				array('version' => 'auto', 'relative' => true)
			);
		}
	}
}
