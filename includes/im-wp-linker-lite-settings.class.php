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

//////////////////////////////////////////
//////////////////////////////////////////
// Класс сохранения настроек
//////////////////////////////////////////
//////////////////////////////////////////
class IMWPLinkerLiteSettings
{
	//////////////////////////////////////////
	// Получить настройки
	//////////////////////////////////////////
	public function get($name = '', $default = '')
	{
		$settings = get_option('im-wp-linker-lite-settings', array());
		if ($name == '') {
			return $settings;
		}
		return isset($settings[$name]) 
			? $settings[$name]
			: $default
		;
	}
	
	//////////////////////////////////////////
	// Сохранить настройки
	//////////////////////////////////////////
	public function save($settings, $name = '')
	{
		$curr_settings = '';
		
		if ($name == '') {
			$curr_settings = $this->get();
			$curr_settings = array_merge($curr_settings, $settings);
			update_option('im-wp-linker-lite-settings', $curr_settings);
		} else {
			$curr_settings = $this->get();
			$curr_settings[$name] = $settings;
			update_option('im-wp-linker-lite-settings', $curr_settings);
		}
		
		wp_cache_flush();
	}
	
	//////////////////////////////////////////
	// Получить отдельную настройку
	//////////////////////////////////////////
	public static function getValue($settings, $name, $default = '')
	{
		if(isset($settings) && is_array($settings)) {
			if (isset($settings[$name])) {
				return $settings[$name];
			}
		}
		return $default;
	}
}