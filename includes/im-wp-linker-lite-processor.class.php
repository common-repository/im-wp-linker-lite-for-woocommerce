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

//////////////////////////////////////////
//////////////////////////////////////////
// Основной класс процессинга
//////////////////////////////////////////
//////////////////////////////////////////
class IMWPLinkerLiteProcessor
{
	protected $tablePrefix = 'im_wp_linker_lite';
	protected $settingsProvider;
	
	function __construct()
	{
		$this->settingsProvider = new IMWPLinkerLiteSettings();
	}

	//////////////////////////////////////////
	// Получить префикс для таблиц
	//////////////////////////////////////////
	public function getPrefix()
	{
		global $wpdb;
		
		return $wpdb->get_blog_prefix() . $this->tablePrefix;
	}
	
	//////////////////////////////////////////
	// Сохранить настройки
	//////////////////////////////////////////
	public function process()
	{
		global $wpdb;
		
		// Получаем основные настройки
		$cats = $this->settingsProvider->get('cat', array());
		$type_write = (int)$this->settingsProvider->get('type_write', '0');
		
		// Очистим временную таблицу
		$wpdb->query(
			'truncate table `' . $this->getPrefix() . '_related_temp`;'
		);

		// Составим временную таблицу
		$wherePart = '';

		$wherePart .= $this->getWhereFilterList(
			array('cats' => $cats), 
			'cats',
			' t.term_id ',
			$wherePart
		);		

		$query = 
			'insert into `' . $this->getPrefix() . '_related_temp` (post_id, number) '
			. ' select distinct p.ID, 0 '
			. ' from `' . $wpdb->posts . '` as p '
				. ' join `' . $wpdb->term_relationships . '` as tr '
					. ' on tr.object_id = p.ID '
				. ' join `' . $wpdb->term_taxonomy . '` as tt '
					. ' on tt.term_taxonomy_id = tr.term_taxonomy_id '
						. ' and tt.taxonomy = "product_cat" '
				. ' join `' . $wpdb->terms . '` t '
					. ' on tt.term_id = t.term_id '
			. ( $wherePart == '' ? ' where 1 = 0 ': ( ' where ' . $wherePart ) )
			. ' order by p.ID asc '
		;
		$wpdb->query( $query );

		
		// Если не режим добавления, то необходима очистка
		if ($type_write != 1) {
			$query = 
				'delete pr '
				. ' from `' . $this->getPrefix() . '_related` pr '
					. ' join `' . $this->getPrefix() . '_related_temp` filter'
						. ' on filter.post_id = pr.post_id ' 
							. ' and pr.type_id = 0 '
			;
			$wpdb->query( $query );
		}
		
		// Если не очистка, то осуществляем напонение
		if ($type_write != 2) {
			// Упорядочим продукты
			$query = 
				'update `' . $this->getPrefix() . '_related_temp` as data '
					. ' join '
					. ' ( '
						. ' select @rownum:=@rownum+1 number, `post_id` '
						. ' from `' . $this->getPrefix() . '_related_temp` as data '
							. ' cross join (select @rownum := 0) rn '
					. ' ) AS r '
						. ' ON data.post_id = r.post_id '
				. ' set data.number = r.number '
			;
			$wpdb->query( $query );
			
			// Получаем общее количество элементв
			$item_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM `" . $this->getPrefix() . "_related_temp`;"
			);

			$item_before = (int)$this->settingsProvider->get('item_before', '3');
			$item_after = (int)$this->settingsProvider->get('item_after', '3');
			
			// Формируем итоговый запрос
			$query = 
				'insert into `' . $this->getPrefix() . '_related` '
					. ' (type_id, post_id, related_post_id) '
				. ' select '
					. ' 0, '
					. ' p.post_id, '
					. ' data.post_id '
				. ' from `' . $this->getPrefix() . '_related_temp` p '
					. ' join `' . $this->getPrefix() . '_related_temp` data '
						. ' on p.post_id != data.post_id '
							. ' and ( '
								// Внутри диапазона
								. ' ( '
									. ' data.number + ' . (int)$item_before . ' >= p.number '
									. ' and data.number <= p.number + ' . (int)$item_after
								. ' ) '
								// Край снизу
								. ' or ( '
									. ' p.number <= ' . (int)$item_before . ' '
									. ' and data.number >= ' . (int)$item_count 
															. ' + p.number - ' . (int)$item_before
								. ' ) '
								// Край сверху
								. ' or ( '
									. ' p.number + ' . (int)$item_after . ' > ' . (int)$item_count
									. ' and data.number <= ' . ' p.number + ' . (int)$item_after 
															. ' - ' . (int)$item_count
								. ' ) '
							. ' ) '
							// Если добавление
							. (
								$type_write == 1
								? (
									' and not exists ( '
										. ' select * '
										. ' from `' . $this->getPrefix() . '_related` check_pr '
										. ' where check_pr.post_id = p.post_id '
											. ' and check_pr.type_id = 0 '
											. ' and check_pr.related_post_id = data.post_id '
									. ' ) '
								)
								: ''
							)
			;

			$wpdb->query( $query );
		}
	}

	//////////////////////////////////////////
	// Сохранить настройки
	//////////////////////////////////////////
	// Составление фильтра для списка
	public function getWhereFilterList($data, $list_name, $clause, $wherePart)
	{
		global $wpdb;
		
		$result = '';

		if (!isset($data[$list_name]))
			return $result;
		
		// Если есть , что фильтровать
		if (count($data[$list_name]) > 0 && !empty($data[$list_name][0]) 
				&& (('' . $data[$list_name][0] != '-1') || count($data[$list_name]) > 1)) {
			$result .= (empty($wherePart) ? '' : ' and ');
			
			if (!is_array($data[$list_name])) {
				$result .= $clause . ' = ' . $wpdb->prepare('%d', $data[$list_name]);
			}
			else if (count($data[$list_name]) == 1) {
				$result .= $clause . ' = ' . $wpdb->prepare('%d', $data[$list_name][0]);
			}
			else {
				for ($cnt = 0; $cnt < count($data[$list_name]); $cnt++) {
					$data[$list_name][$cnt] = $wpdb->prepare('%d', $data[$list_name][$cnt]);
				}
				$result .= $clause . ' in (' . join(', ', $data[$list_name]) . ') ';
			}
		}
	
		return $result;
	}
}