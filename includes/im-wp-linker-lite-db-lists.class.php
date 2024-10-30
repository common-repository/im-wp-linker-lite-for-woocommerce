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
// Класс получения списка категории из БД
//////////////////////////////////////////
//////////////////////////////////////////
class IMWPLinkerLiteDBLists
{
	// Получение списка категорий
	public function getCategories($parent_id = 0, $parent_prefix = '') 
	{
		$category_data = array();

		$taxonomy     = 'product_cat';
		$orderby      = 'name';  
		$show_count   = 0;
		$pad_counts   = 0; 
		$hierarchical = 1; 
		$title        = '';  
		$empty        = 0;
		$child_of     = 0;
		
		$args = array(
		  'taxonomy'     => $taxonomy,
		  'orderby'      => $orderby,
		  'show_count'   => $show_count,
		  'pad_counts'   => $pad_counts,
		  'hierarchical' => $hierarchical,
		  'title_li'     => $title,
		  'hide_empty'   => $empty,
		  'parent' => (int)$parent_id,
		  'child_of' => $child_of,
		);		
		
		$get_cats = get_categories( $args );
        
		if($get_cats) {
			// Формируем результирующий массив
			foreach ($get_cats as $result) {
				$category_data[] = array(
					'id' => $result->term_id,
					'name' => $parent_prefix . $result->name,
				);
			
				$category_data = array_merge(
					$category_data, 
					$this->getCategories($result->term_id, $parent_prefix . $result->name . ' > ')
				);
			}	
			
		}
		
		return $category_data;
	}

	// Получение списка категорий
	public function getTreeCategories($parent_id = 0) 
	{
		$category_data = array();

		$taxonomy     = 'product_cat';
		$orderby      = 'name';  
		$show_count   = 0;
		$pad_counts   = 0; 
		$hierarchical = 1; 
		$title        = '';  
		$empty        = 0;
		$child_of     = 0;
		
		$args = array(
		  'taxonomy'     => $taxonomy,
		  'orderby'      => $orderby,
		  'show_count'   => $show_count,
		  'pad_counts'   => $pad_counts,
		  'hierarchical' => $hierarchical,
		  'title_li'     => $title,
		  'hide_empty'   => $empty,
		  'parent' => (int)$parent_id,
		  'child_of' => $child_of,
		);		
		
		$get_cats = get_categories( $args );
        
		if($get_cats) {
			// Формируем результирующий массив
			foreach ($get_cats as $result) {
				$category_data[] = array(
					'id' => $result->term_id,
					'name' => $result->name,
					'childs' => $this->getTreeCategories($result->term_id)
				);
			}	
			
		}
		
		return $category_data;
	}

}