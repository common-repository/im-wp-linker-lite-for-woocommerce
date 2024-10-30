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
require_once (IM_WP_LINKER_LITE_DIR . 'includes/im-wp-linker-lite-db-lists.class.php');
require_once (IM_WP_LINKER_LITE_DIR . 'includes/im-wp-linker-lite-processor.class.php');
require_once (IM_WP_LINKER_LITE_DIR . 'includes/im-wp-linker-lite-html-helper.class.php');

class IMWPLinkerLiteAdmin
{
	protected $htmlHelper;
	protected $settingsProvider;
	
	function __construct()
	{
		$this->htmlHelper = new IMWPLinkerLiteHtmlHelper();
		$this->settingsProvider = new IMWPLinkerLiteSettings();
		
		$type_upsell_display = (int)$this->settingsProvider->get('type_upsell_display', '0');
		
		if ($type_upsell_display > 0) {
			$this->includeAdminProduct();
		}
	}

	protected function includeAdminProduct()
	{
		require_once (IM_WP_LINKER_LITE_DIR . 'includes/im-wp-linker-lite-admin-product.php');
	}

	// Стили формы
	public function cssRegister()
	{
		wp_register_style(
			'admin-style-im-wp-linker-lite', 
			plugins_url('/assets/css/admin-style.css', dirname(__FILE__))
		);
		wp_enqueue_style('admin-style-im-wp-linker-lite');
	}

	// Скрипты
	public function scriptRegister()
	{
		wp_register_script(
			'admin-script-im-wp-linker-lite', 
			plugins_url('/assets/js/jquery.im-wp-linker-lite.html.helper.js', dirname(__FILE__))
		);
		wp_enqueue_script('admin-script-im-wp-linker-lite');
	}

	
	public function admin_menu() 
	{
		$page_suffix = add_submenu_page(
			'options-general.php', 
			__('IM WP Linker Lite', 'im-wp-linker-lite'), 
			__('IM WP Linker Lite', 'im-wp-linker-lite'), 
			5,
			IM_WP_LINKER_LITE_FILE, 
			array($this, 'plugin_menu')
		);

		/*
		 * Создаем хуки, содержащий суффикс созданной страницы настроек $page_suffix
		 */
		add_action( 'admin_print_styles-' . $page_suffix, array($this, 'cssRegister') );
		add_action( 'admin_print_scripts-' . $page_suffix, array($this, 'scriptRegister') );
	}
	
	protected function getSanitizePostData($data) 
	{
		global $wpdb;
		
		$resultData = array();
		
		foreach ($data as $key => $item)
		{
			if (is_array($item)) {
				$tempArray = array();
				
				foreach($item as $tempItem) {
					$tempArray[] = $wpdb->prepare( '%d', $tempItem );
				}
						
				$resultData[$key] = $tempArray;
			} else {
				$resultData[$key] = (int)$item;
			}
		}
		
		if (isset($resultData['item_before'])) {
			$resultData['item_before'] = max(0, (int)$resultData['item_before']);
		}

		if (isset($resultData['item_after'])) {
			$resultData['item_after'] = max(0, (int)$resultData['item_after']);
		}
		
		if (isset($resultData['type_upsell_display'])) {
			$temp = (int)$resultData['type_upsell_display'];
			
			if ($temp < 0 || $temp > 2) {
				$temp = 0;
			}
			
			$resultData['type_upsell_display'] = $temp;
		}

		if (isset($resultData['type_write'])) {
			$temp = (int)$resultData['type_write'];
			
			if ($temp < 0 || $temp > 2) {
				$temp = 0;
			}
			
			$resultData['type_write'] = $temp;
		}
		
		return $resultData;
	}
	
	public function plugin_menu()
	{
		$message = '';

		// Сохранение настроек
		if (isset($_POST['action']) && $_POST['action'] == 'update_im_wp_linker_lite_form'
			&& isset($_POST['im_wp_linker_lite']) && !empty($_POST['im_wp_linker_lite']))
		{
			$correctData = $this->getSanitizePostData($_POST['im_wp_linker_lite']);
			$this->settingsProvider->save($correctData);

			$message = __('Настройки сохранены', 'im-wp-linker-lite');
		} 
		else if (isset($_POST['action']) && $_POST['action'] == 'process_im_wp_linker_lite_form'
			&& isset($_POST['im_wp_linker_lite']) && !empty($_POST['im_wp_linker_lite'])) 
		{
			$correctData = $this->getSanitizePostData($_POST['im_wp_linker_lite']);
			$this->settingsProvider->save($correctData);
			
			$processor = new IMWPLinkerLiteProcessor();
			$processor = $processor->process();

			$message = __('Последняя конфигурация сохранена и генерация выполнена', 'im-wp-linker-lite');
		}
		
		$this->htmlMessage($message);
		
		$this->htmlHeader();
		
		$this->htmlOpenForm();

		$this->htmlBodyGenForm($this->settingsProvider);

		$this->htmlCloseForm(
			'process_im_wp_linker_lite_form',
			 __('Сгенерировать', 'im-wp-linker-lite')
		);
		
		$this->htmlOpenForm();

		$this->htmlBodySettingsForm($this->settingsProvider);

		$this->htmlCloseForm(
			'update_im_wp_linker_lite_form',
			 __('Сохранить настройки', 'im-wp-linker-lite')
		);

		$this->htmlCopyright();
	}
	
	protected function htmlBodySettingsForm(&$settingsProvider)
	{
		echo '<tr><th colspan="2" class="im-wp-lite-th-h3"><h3 class="im-wp-lite-h3">' 
				. __('Настройки отображения и функционирования', 'im-wp-linker-lite') 
			. '</th></tr>'
		;
		
		$settings = $settingsProvider->get();
		echo $this->htmlHelper->echoSelectField(
			array(
				array( 'id' => 0, 'name' => __('Обычный режим WooCommerce', 'im-wp-linker-lite') ),
				array( 'id' => 1, 'name' => __('Использование только IM WP Linker Lite', 'im-wp-linker-lite') ),
				array( 'id' => 2, 'name' => __('Смешанный режим (WC + Linker)', 'im-wp-linker-lite') ),
			),
			'type_upsell_display',
			__('Тип Апсейл', 'im-wp-linker-lite'),
			IMWPLinkerLiteSettings::getValue($settings, 'type_upsell_display', '')
		);
		
		// Количество апсейлов
		echo $this->htmlHelper->echoInputField(
			'upsell_max', 
			htmlspecialchars(
				__(
					'Количество выводимых товаров (чтобы вывести все - поставьте 0 или меньше)', 
					'im-wp-linker-lite'
				),
				ENT_NOQUOTES
			),
			IMWPLinkerLiteSettings::getValue($settings, 'upsell_max', '-1')
		);
		
		// Количество колонок
		echo $this->htmlHelper->echoInputField(
			'upsell_cols', 
			htmlspecialchars(
				__(
					'Количество колонок (чтобы вывести по умолчанию - поставьте 0 или меньше)', 
					'im-wp-linker-lite'
				),
				ENT_NOQUOTES
			),
			IMWPLinkerLiteSettings::getValue($settings, 'upsell_cols', '-1')
		);
	}
	
	protected function htmlBodyGenForm(&$settingsProvider)
	{
		$settings = $settingsProvider->get();
		$dbListProvider = new IMWPLinkerLiteDBLists();
		

		echo '<tr><th colspan="2" class="im-wp-lite-th-h3"><h3 class="im-wp-lite-h3">' 
				. __('Настройки генерации', 'im-wp-linker-lite') 
			. '</th></tr>'
		;

		$catList = $dbListProvider->getTreeCategories();
		echo $this->htmlHelper->echoSelectFieldWPStyle(
			$catList,
			'cat',
			htmlspecialchars(
				__('Выберите категории', 'im-wp-linker-lite'),
				ENT_NOQUOTES
			),
			IMWPLinkerLiteSettings::getValue($settings, 'cat', array())
		);

		echo $this->htmlHelper->echoSelectField(
			array(
				array( 'id' => 0, 'name' => __('Перезапись', 'im-wp-linker-lite') ),
				array( 'id' => 1, 'name' => __('Режим добавления', 'im-wp-linker-lite') ),
				array( 'id' => 2, 'name' => __('Очистка', 'im-wp-linker-lite') ),
			),
			'type_write',
			__('Тип записи или очистка', 'im-wp-linker-lite'),
			IMWPLinkerLiteSettings::getValue($settings, 'type_write', '')
		);

		echo $this->htmlHelper->echoInputField(
			'item_before', 
			htmlspecialchars(
				__('Количество товаров "ДО"', 'im-wp-linker-lite'),
				ENT_NOQUOTES
			),
			IMWPLinkerLiteSettings::getValue($settings, 'item_before', '3')
		);

		echo $this->htmlHelper->echoInputField(
			'item_after', 
			htmlspecialchars(
				__('Количество товаров "ПОСЛЕ"', 'im-wp-linker-lite'),
				ENT_NOQUOTES
			),
			IMWPLinkerLiteSettings::getValue($settings, 'item_after', '3')
		);

	}

	protected function htmlHeader()
	{
		$echoHtml = '';
		
		$echoHtml .=
			'<div id="dropmessage" class="updated" style="display:none;"></div>'
			. '<div class="wrap">'
				. '<h2 class="im-wp-lite-h2">' . __('IM WP Linker Lite - Настройки', 'im-wp-linker-lite') . '</h2>'
				. '<p>'
					. '<span>'
						. __('Генератор сео перелинковки продуктов (SEO)', 'im-wp-linker-lite')
					. '</span>' 
					. '<br>'
					. '<span class="im-wp-lite-info">'
						. __('Если вам нужна платная полная версия плагина, то обращайтесь.', 'im-wp-linker-lite')
					. '</span>' 
					. '<br>'
					. '<span>'
						. '[ '
							. ' dev.imirochnik@gmail.com '
							. ' | <a href="http://ida-freewares.ru" target="_blank">Ida-Freewares.ru</a> '
							. ' | <a href="http://IM-Cloud.ru" target="_blank">IM-Cloud.ru</a> '
						. ']' 
					. '</span>' 
				. '</p>'
			. '</div>'
		;
		
		echo $echoHtml;
	}
	

	protected function htmlMessage($message)
	{
		$echoHtml = '';
		
		if (isset($message) && !empty($message)) {
			$echoHtml .= '<div id="message" class="updated fade"><p>'
				. $message
				. '</p></div>'
			;
		}
		
		echo $echoHtml;
	}

	// Начало формы
	protected function htmlOpenForm($appendClass = '') 
	{
		$echoHtml = '';
		
		$echoHtml .=
			'<div class="wrap im-wp-linker-lite-form-container ' 
				. esc_attr( $appendClass ? $appendClass : '' )
			. '">'
				. '<form action="" method="post">'
					. '<div class="postbox">'
						. '<div class="inside">'
							. '<table class="form-table">'
		;
		
		echo $echoHtml;
	}

	// Конец формы
	protected function htmlCloseForm($action = '', $name = '') 
	{
		$echoHtml = '';
		
		$echoHtml .=
							'</table>'
						. '</div>'
					. '</div>'
					. '<p class="submit" style="text-align:center">'
						. '<input type="hidden" name="action" value="' . esc_attr($action) . '" />'
						. '<input type="submit" class="im-wp-linker-lite-btn" name="Submit" '
							. 'value="'
								. esc_attr($name)
							. '" />'
					. '</p>'
				. '</form>'
			. '</div>'
		;
		
		echo $echoHtml;
	}

	protected function htmlCopyright()
	{
		$echoHtml = 
			'<div class="im-wp-lite-author">'
				. __('Игорь Мирочник &copy; IM WP Linker Lite', 'im-wp-linker-lite') 
				. ' ver. ' . IM_WP_LINKER_LITE_VERSION
			. '</div>'
		;
		
		echo $echoHtml;
	}

}

$IMWPLinkerLiteAdminSingle = new IMWPLinkerLiteAdmin();

add_action('admin_menu', array($IMWPLinkerLiteAdminSingle, 'admin_menu'));
