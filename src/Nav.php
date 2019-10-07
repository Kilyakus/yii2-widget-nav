<?php
namespace kilyakus\nav;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\BootstrapAsset;

/*
	For example:

	echo Nav::widget([
		'items' => [
			[
				'icon' => 'fa fa-warning',
				'badge' => Badge::widget(['label' => 'New', 'round' => false]),
				'label' => 'Home',
				'url' => ['site/index'],
				'linkOptions' => [...],
			],
			[
				'label' => 'Dropdown',
				'items' => [
					 ['label' => 'Level 1 - Dropdown A', 'url' => '#'],
					 '<li class="divider"></li>',
					 '<li class="dropdown-header">Dropdown Header</li>',
					 ['label' => 'Level 1 - Dropdown B', 'url' => '#'],
				],
			],
		],
	]);

	Note: Multilevel dropdowns beyond Level 1 are not supported in Bootstrap 3.

*/

class Nav extends \yii\bootstrap\Nav
{
	const POS_DEFAULT = '';
	const POS_LEFT = 'pull-left';
	const POS_RIGHT = 'pull-right';

	const TYPE_DEFAULT = '';
	const TYPE_NOTIFICATION = 'notification';
	const TYPE_INBOX = 'inbox';
	const TYPE_TASKS = 'tasks';
	const TYPE_USER = 'user';

	const NAVBAR_NONE = '';
	const NAVBAR_DEFAULT = 'kt-nav';

	const ITEM_DIVIDER = 'divider';

	public $items = [];

	public $position = self::POS_DEFAULT;

	public $dropdownType = self::TYPE_DEFAULT;

	public $navbar = self::NAVBAR_DEFAULT;

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		Html::addCssClass($this->options, $this->navbar);
		Html::addCssClass($this->options, $this->position);
		parent::init();
	}

	public function run()
	{
		NavAsset::register($this->getView());
		BootstrapAsset::register($this->getView());
		return $this->renderItems();
	}

	/**
	 * Renders a widget's item.
	 * @param string|array $item the item to render.
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	public function renderItem($item)
	{
		if (is_string($item)) {
			return $item;
		}

		if (array_key_exists(self::ITEM_DIVIDER, $item))
		{
			return Html::tag('li', '', ['class' => self::ITEM_DIVIDER]);
		}

		if (!isset($item['label']) && !isset($item['icon'])) {
			throw new InvalidConfigException("The 'label' option is required.");
		}
		$encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
		$label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
		$options = ArrayHelper::getValue($item, 'options', []);
		$items = ArrayHelper::getValue($item, 'items');
		$url = ArrayHelper::getValue($item, 'url', '#');
		$linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);

		if (isset($item['active'])) {
			$active = ArrayHelper::remove($item, 'active', false);
		} else {
			$active = $this->isItemActive($item);
		}

		if (isset($items)) {
			$linkOptions['data-toggle'] = 'dropdown';
			Html::addCssClass($options, ['widget' => 'dropdown']);
			Html::addCssClass($linkOptions, ['widget' => 'dropdown-toggle']);
			if ($this->dropDownCaret !== '') {
				$label .= ' ' . $this->dropDownCaret;
			}
			if (is_array($items)) {
				$items = $this->isChildActive($items, $active);
				$items = $this->renderDropdown($items, $item);
			}
		}

		if ($active) {
			Html::addCssClass($options, 'active');
		}

		return Html::tag('li', sprintf('%s%s', $this->_getLinkTag($item), $this->_getDropdownTag($item)), $options);

		// if (is_string($item))
		// {
		//	 return $item;
		// }

		// if (array_key_exists(self::ITEM_DIVIDER, $item))
		// {
		//	 return Html::tag('li', '', ['class' => self::ITEM_DIVIDER]);
		// }

		// $items = ArrayHelper::getValue($item, 'items');
		
		// if ($items === null)
		// {
		//	 return parent::renderItem($item);
		// }

		// if (!isset($item['label']) && !isset($item['icon']))
		// {
		//	 throw new InvalidConfigException("The 'label' option is required.");
		// }
		
		// $dropdownType = ArrayHelper::getValue($item, 'dropdownType', self::TYPE_DEFAULT);
		// $options = ArrayHelper::getValue($item, 'options', []);

		// Html::addCssClass($options, 'dropdown');

		// if ($dropdownType !== self::TYPE_DEFAULT)
		// {
		//	 if ($dropdownType !== self::TYPE_USER)
		//	 {
		//		 Html::addCssClass($options, 'dropdown-extended');
		//	 }

		//	 Html::addCssClass($options, 'dropdown-'.$dropdownType);

		//	 // yii2-template-engine - задумка сделать смену дизайна и возможность настроить связь между всеми виджетами
		//	 // if (Engine::HEADER_DROPDOWN_DARK === Engine::getComponent()->headerDropdown)
		//	 // {
		//	 //	 Html::addCssClass($options, 'dropdown-dark');
		//	 // }
		// }

		// if (isset($item['active']))
		// {
		//	 $active = ArrayHelper::remove($item, 'active', false);
		// }
		// else
		// {
		//	 $active = $this->isItemActive($item);
		// }

		// if ($active)
		// {
		//	 Html::addCssClass($options, 'active');
		// }
		
		// return Html::tag('li', sprintf('%s%s', $this->_getLinkTag($item), $this->_getDropdownTag($item)), $options);
	}

	/**
	 * Retrieves link tag
	 * @param array $item given item
	 * @return string link
	 */
	private function _getLinkTag($item)
	{
		$dropdownType = ArrayHelper::getValue($item, 'dropdownType', self::TYPE_DEFAULT);

		$label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];

		$label = Html::tag('span', $label, ['class' => 'kt-nav__link-text']);

		$icon = ArrayHelper::getValue($item, 'icon', null);

		if ($icon)
		{
			$label = Html::tag('span', '', ['class' => 'kt-nav__link-icon ' . $icon]) . ' ' . $label;
		}

		$label .= ArrayHelper::getValue($item, 'badge', '');

		$linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);

		if(isset($item['items'])){

			$linkOptions['data-toggle'] = 'dropdown';
			$linkOptions['data-hover'] = 'dropdown';
			$linkOptions['data-close-others'] = 'true';

			Html::addCssClass($linkOptions, 'dropdown-toggle');
		}

		$url = ArrayHelper::getValue($item, 'url', false);

		if (!$url)
		{
			return Html::a($label, 'javascript://', $linkOptions);
		}

		return Html::a($label, Url::toRoute(ArrayHelper::getValue($item, 'url', '#')), $linkOptions);
	}

	/**
	 * Retrieves items tag
	 * @param array $item given parent item
	 * @return Dropdown widget
	 */
	private function _getDropdownTag($item)
	{
		$dropdownType = ArrayHelper::getValue($item, 'dropdownType', self::TYPE_DEFAULT);

		$items = ArrayHelper::getValue($item, 'items', null);

		if ($items !== null && is_array($items))
		{
			if ($dropdownType === self::TYPE_DEFAULT || $dropdownType === self::TYPE_USER)
			{
				$options = ['class' => 'dropdown-menu-default'];
			}
			else
			{
				$options = ['class' => sprintf('%s %s', 'dropdown-menu-default extended', $dropdownType)];
			}

			$items = \kilyakus\web\widgets\Dropdown::widget([
					'title' => ArrayHelper::getValue($item, 'title', ''),
					'more' => ArrayHelper::getValue($item, 'more', []),
					'scroller' => ArrayHelper::getValue($item, 'scroller', []),
					'items' => $items,
					'encodeLabels' => $this->encodeLabels,
					'clientOptions' => false,
					'options' => $options,
			]);
		}

		return $items;
	}

	/**
	 * Renders user item.
	 * @param $label string User label
	 * @param $photo string User photo url
	 * @return string the rendering result
	 */
	public static function userItem($label, $photo)
	{
		$lines = [];
		$lines[] = Html::tag('span', $label, ['class' => 'username username-hide-on-mobile']);
		$lines[] = Html::img($photo, ['alt' => $label, 'class' => 'img-circle']);
		return implode("\n", $lines);
	}
}