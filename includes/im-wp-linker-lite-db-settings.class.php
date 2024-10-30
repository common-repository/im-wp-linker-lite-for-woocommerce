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
// Класс сохранения настроек c БД
//////////////////////////////////////////
//////////////////////////////////////////
class IMWPLinkerLiteDBSettings
{
	protected $tablePrefix = 'im_wp_linker_lite';

	//////////////////////////////////////////
	// Получить префикс для таблиц
	//////////////////////////////////////////
	public function getPrefix()
	{
		global $wpdb;
		
		return $wpdb->get_blog_prefix() . $this->tablePrefix;
	}

	//////////////////////////////////////////
	// Инициализация и создание таблиц
	//////////////////////////////////////////
	public function install()
	{
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charsetCollate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

		// Таблица под хранение связей
		$tableName = $this->getPrefix() . '_related';
		$sql = "CREATE TABLE {$tableName} (
			type_id bigint(20) unsigned NOT NULL default '0',
		    post_id bigint(20) unsigned NOT NULL,
		    related_post_id bigint(20) unsigned NOT NULL,
		    PRIMARY KEY  (type_id, post_id, related_post_id),
		    KEY type_search (type_id, post_id)
		) {$charsetCollate};";

		// Создать таблицу.
		dbDelta( $sql );
		
		// Таблица под генерацию связей
		$tableName = $this->getPrefix() . '_related_temp';
		$sql = "CREATE TABLE {$tableName} (
		    post_id bigint(20) unsigned NOT NULL,
		    number bigint(20) unsigned NOT NULL
		) {$charsetCollate};";

		// Создать таблицу.
		dbDelta( $sql );
	}
	
	//////////////////////////////////////////
	// Сохранения связанных продуктов из админки
	//////////////////////////////////////////
	public function saveProductRelatedFromAdminPage($postID, $relatedPosts)
	{
		global $wpdb;
		$this->deleteProductRelatedFromAdminPage($postID);
		
		$tableName = $this->getPrefix() . '_related';
		
		foreach($relatedPosts as $related_id) {
			$wpdb->insert(
				$tableName,
				array( 'post_id' => $postID, 'related_post_id' => $related_id ),
				array( '%d', '%d' )
			);
		}
	}

	//////////////////////////////////////////
	// Удаление связанных продуктов из админки
	//////////////////////////////////////////
	public function deleteProductRelatedFromAdminPage($postID)
	{
		global $wpdb;
		
		$tableName = $this->getPrefix() . '_related';
		$wpdb->delete( 
			$tableName, 
			array( 'post_id' => $postID ),
			array( '%d' )
		);
	}

	//////////////////////////////////////////
	// Получение связанных продуктов из админки
	//////////////////////////////////////////
	public function getProductRelatedFromAdminPage($postID)
	{
		global $wpdb;
		
		$tableName = $this->getPrefix() . '_related';
		$query = $wpdb->prepare(
			' select * '
			. ' from `' . $tableName . '` '
			. ' where type_id = 0 '
				. ' and post_id = %d ',
		    $postID
		);
		$result = $wpdb->get_results( $query );
		
		return $result;
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