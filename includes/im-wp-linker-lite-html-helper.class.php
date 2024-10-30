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
// Хэдпер для построения HTML
//////////////////////////////////////////
//////////////////////////////////////////
class IMWPLinkerLiteHtmlHelper
{

	//////////////////////////////////////////
	// Поле ввода
	//////////////////////////////////////////
	public function echoInputField($name, $desc = '', $curvalue = '', $class = '')
	{
		$result = '<tr>';
		
		$result .= '<th scope="row" style="width: 30%;">'
					. '<label>'
						. ($desc == '' ? $name : $desc)
					. '</label>'
				. '</th>'
		;
		
		$result .= '<td style="vertical-align: top; padding-top: 20px;">'
					. '<input type="text" class="' . esc_attr($class) . '" '
						. ' style="width:100%;" '
						. ' name="im_wp_linker_lite[' . esc_attr($name) . ']" '
						. ' value="' . esc_attr($curvalue) . '" '
					. '/>'
				. '</td>'
		;
		
		return $result . '</tr>';
	}	


	//////////////////////////////////////////
	// Поле ввода textarea
	//////////////////////////////////////////
	public function echoTextareaField($name, $desc = '', $curvalue = '', $class = '')
	{
		$result = '<tr>';
		
		$result .= '<th scope="row" style="width: 30%;">'
					. '<label>'
						. ($desc == '' ? $name : $desc)
					. '</label>'
				. '</th>'
		;
		
		$result .= '<td style="vertical-align: top; padding-top: 20px;">'
					. '<textarea cols="80" rows="5" '
						. ' style="width: 100%;" '
						. ' class="' . esc_attr($class) . '" '
						. ' name="im_wp_linker_lite[' . esc_attr($name) . ']" '
					. '>'
						. esc_textarea($curvalue)
					. '</textarea>'
				. '</td>'
		;
		
		return $result . '</tr>';
	}	

	//////////////////////////////////////////
	// Поле select
	//////////////////////////////////////////
	public function echoSelectField(
		$data, $name, $desc = '', $curvalue = '', $class = '',
		$is_multi = false,
		$field_id = 'id', $field_name = 'name'
	) {
		
		$result = '<tr>';
		
		$result .= '<th scope="row" style="width: 30%;">'
					. '<label>'
						. ($desc == '' ? $name : $desc)
					. '</label>'
				. '</th>'
		;
		
		$result .= '<td style="vertical-align: top; padding-top: 20px;">'
			. '<select'
				. ($is_multi ? ' multiple="multiple" ' : '')
				. ' style="width: 100%;" '
				. ' class="' . esc_attr($class) . '" '
				. ' name="im_wp_linker_lite[' . esc_attr($name) . ']'
					. ($is_multi ? '[]' : '')
				. '" '
			. '>'
		;

		foreach($data as $item) {
			if (!isset($item[$field_id])) {
				continue;
			}
			if (''.$item[$field_id] == ''.$curvalue) {
				$result .=
					'<option selected="selected" value="' . esc_attr($item[$field_id]) . '" >'
						. esc_html($item[$field_name])
					. '</option>'
				;
			} else {
				$result .=
					'<option value="' . esc_attr($item[$field_id]) . '" >'
						. esc_html($item[$field_name])
					. '</option>'
				;
			}
		}		
		
		$result .= 
				'</select>'
			. '</td>'
		;
		
		return $result . '</tr>';
	}	

	//////////////////////////////////////////
	// Поле select
	//////////////////////////////////////////
	public function echoSelectFieldWPStyle(
		$data, $name, $desc = '', $cur_vals = array(), $append_class = '',
		$field_id = 'id', $field_name = 'name', $field_childs = 'childs'
	) {
		
		$result = '<tr>';
		
		$result .= '<th scope="row" style="width: 30%;">'
					. '<label>'
						. ($desc == '' ? $name : $desc)
					. '</label>'
				. '</th>'
		;
		
		$result .= '<td style="vertical-align: top; padding-top: 20px;">'
			. '<div class="categorydiv">'
				. '<div class="tabs-panel">'
		;
		
		$cb_name = 'im_wp_linker_lite[' . esc_attr($name) . '][]';
		
		$result .= 
			'<input '
				. ' type="hidden" '
				. ' name="' . esc_attr($cb_name) . '" '
				. ' value="-1" '
			. '/>'
		;
		
		// Отображаем дерево
		$result .= $this->_treeWPStyleRecurs(
			$data,
			$cb_name,
			$cur_vals,
			$append_class,
			0,
			$field_id,
			$field_name,
			$field_childs
		);

		$result .= 
					'</div>'
				. '</div>'
				. '<div class="im-wp-linker-lite-choose">'
  					. '<a class="im-wp-linker-lite-choose-all" href="#">Выбрать все</a>'
  					. ' / '
  					. '<a class="im-wp-linker-lite-choose-clear" href="#">Снять все</a>'
				. '</div>'
			. '</td>'
		;
		
		return $result . '</tr>';
	}	

	//////////////////////////////////////////
	// Древовидное отображение
	//////////////////////////////////////////
	protected function _treeWPStyleRecurs(
		&$data, &$cb_name, &$curr_vals, &$append_class, $level = 0,
		&$field_id = 'id', &$field_name = 'name', &$field_childs = 'childs'
	) {
		if (!isset($data) || !is_array($data) || count($data) <= 0) {
			return '';
		}
		
		$result = 
			'<ul ' 
				. (
					$level == 0
					? (
						' class="categorychecklist form-no-clear ' 
							. esc_attr($append_class ? $append_class : '') . '" ' 
						. ' data-wp-lists="list:category" '
					)
					: ' class="children" '
				)
			.'>'
		;
		
		foreach($data as &$item) {
			$result .=
				'<li>'
					. '<label class="selectit">'
						. '<input '
							. ' type="checkbox" '
							. ' name="' . esc_attr($cb_name) . '" '
							. ' value="' . esc_attr($item[$field_id]) . '" '
							. (
								isset($curr_vals) && is_array($curr_vals) && in_array(esc_attr($item[$field_id]), $curr_vals)
								? ' checked="checked" '
								: ''
							)
						. '/>'
						. esc_html($item[$field_name])
					. '</label>'
					. $this->_treeWPStyleRecurs(
						$item[$field_childs],
						$cb_name,
						$curr_vals,
						$append_class,
						$level + 1,
						$field_id,
						$field_name,
						$field_childs
					)
				. '</li>'
			;
		}
		unset($item);
		
		return $result . '</ul>';
	}
}