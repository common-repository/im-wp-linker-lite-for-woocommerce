<?php
/**
* IM WP Linker
* 
* @author 	Igor Mirochnik
* @site		http://IM-Cloud.ru/
* @site		http://Ida-Freewares.ru/
* @license	GPLv3 or later
* 
*/

/*
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

	(Это свободная программа: вы можете перераспространять ее и/или изменять
	ее на условиях Стандартной общественной лицензии GNU в том виде, в каком
	она была опубликована Фондом свободного программного обеспечения; либо
	версии 3 лицензии, либо (по вашему выбору) любой более поздней версии.

	Эта программа распространяется в надежде, что она будет полезной,
	но БЕЗО ВСЯКИХ ГАРАНТИЙ; даже без неявной гарантии ТОВАРНОГО ВИДА
	или ПРИГОДНОСТИ ДЛЯ ОПРЕДЕЛЕННЫХ ЦЕЛЕЙ. Подробнее см. в Стандартной
	общественной лицензии GNU.

	Вы должны были получить копию Стандартной общественной лицензии GNU
	вместе с этой программой. Если это не так, см.
	<http://www.gnu.org/licenses/>.)
*/

require_once (IM_WP_LINKER_LITE_DIR . 'includes/im-wp-linker-lite-settings.class.php');
require_once (IM_WP_LINKER_LITE_DIR . 'includes/im-wp-linker-lite-db-settings.class.php');

class IMWPLinkerLiteCore
{
	protected $settingsProvider;
	protected $dbSettingProvider;
	protected $typeUpsellDisplay;
	protected $upsaleMax;
	protected $upsellCols;
	protected $isActive;
	
	function __construct()
	{
		$this->settingsProvider = new IMWPLinkerLiteSettings();
		$this->dbSettingProvider = new IMWPLinkerLiteDBSettings();
		
		$this->typeUpsellDisplay = (int)$this->settingsProvider->get('type_upsell_display', '0');
		$this->upsaleMax = (int)$this->settingsProvider->get('upsell_max', '-1');
		$this->upsellCols = (int)$this->settingsProvider->get('upsell_cols', '-1');
		$this->isActive = (int)get_option('im-wp-linker-lite-active', '0');
	}
	/** регистрация фильтров и действий * */
	public function init() 
	{
	    add_filter('woocommerce_product_get_upsell_ids', array($this, 'formUpsellIds'), 20, 2);
	    add_filter('woocommerce_upsells_columns', array($this, 'formColumnsUpsell'), 20, 1);
	    add_filter('woocommerce_upsells_total', array($this, 'formLimitUpsell'), 20, 1);
	}

	public function formColumnsUpsell($columns)
	{
		if ($this->isActive && $this->typeUpsellDisplay > 0 && $this->upsellCols > 0) {
			return $this->upsellCols;
		} else {
			return $columns;
		}
	}

	public function formLimitUpsell($limit)
	{
		if ($this->isActive && $this->typeUpsellDisplay > 0) {
			return $this->upsaleMax;
		} else {
			return $limit;
		}
	}

	public function formUpsellIds($upsell_ids, $instance)
	{
		if (function_exists('is_woocommerce')) {
			if (is_woocommerce() && is_single()) 
			{
				if ($this->isActive && $this->typeUpsellDisplay > 0) {
					$result_ids = array();
					
					if ($this->typeUpsellDisplay == 2) {
						$result_ids = array_merge($result_ids, $upsell_ids);
					}
					
					$linker_ids = $this->dbSettingProvider->getProductRelatedFromAdminPage(
						$instance->get_id()
					);
					
					foreach($linker_ids as $item) {
						if ( in_array( (int)$item->related_post_id, $result_ids ) ) {
							continue;
						}
						$result_ids[] = (int)$item->related_post_id;
					}
					return $result_ids;
				} else {
					return $upsell_ids;
				}
			}
		}
	}
}

$IMWPLinkerLiteCoreSingle = new IMWPLinkerLiteCore();
$IMWPLinkerLiteCoreSingle->init();
