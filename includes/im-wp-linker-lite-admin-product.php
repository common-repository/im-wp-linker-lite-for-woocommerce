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

class IMWPLinkerLiteAdminProduct
{
	protected $settingProvider;
	protected $dbSettingProvider;
	
	function __construct()
	{
		$this->settingProvider = new IMWPLinkerLiteSettings();
		$this->dbSettingProvider = new IMWPLinkerLiteDBSettings();
	}

	public function selectUpsellRelated()
	{
		global $post, $woocommerce;
		
		$product_ids = array();
		
		$temp = $this->dbSettingProvider->getProductRelatedFromAdminPage($post->ID);
		
		foreach($temp as $item) {
			$product_ids[] = $item->related_post_id;
		}
		
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="linker_lite_upsell_related_ids">
					<?php echo __( 'Linker Lite Апсейл продукты', 'im-wp-linker-lite' ); ?>
				</label>
				<select class="wc-product-search" multiple="multiple" 
					style="width: 50%;" 
					id="linker_lite_upsell_related_ids" 
					name="linker_lite_upsell_related_ids[]" 
					data-placeholder="<?php esc_attr_e( 'Найти продукт', 'im-wp-linker-lite' ); ?>" 
					data-action="woocommerce_json_search_products_and_variations" 
					data-exclude="<?php echo intval( $post->ID ); ?>">
					<?php
						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								echo '<option value="' 
										. esc_attr( $product_id ) . '"' 
										. selected( true, true, false ) 
									. '>' 
										. wp_kses_post( $product->get_formatted_name() ) 
									. '</option>'
								;
							}
						}
					?>
				</select> 
				<?php 
					echo wc_help_tip( 
						__( 
							'Здесь отображены связанные продукты с помощью IM WP Linker Lite.',
							'im-wp-linker-lite' 
						) 
					); 
				?>
			</p>
		</div>
		<?php		
	}
	
	public function saveUpsellRelated($postID, $post)
	{
		global $woocommerce;
		
		// Если пост не пустой, то сохраняем
		if (isset($_POST['linker_lite_upsell_related_ids'])) {
			
			$ids_from_post = $_POST['linker_lite_upsell_related_ids'];
			
			$relatedIds = array();
			
			// Проверяем корректность идентификаторов
			if (is_array($ids_from_post)) {
				$temp_posts = get_posts(
					array(
						'post_type' => 'product',
						'include' => $ids_from_post,
					)
				);
				
				foreach ($temp_posts as $item) {
					if (''.$postID != ''.$item->ID) {
						$relatedIds[] = $item->ID;
					}
				}
			}
			
			$this->dbSettingProvider->saveProductRelatedFromAdminPage(
				$postID,
				$relatedIds
			);
		}
		// Иначе удалить
		else {
			$this->dbSettingProvider->deleteProductRelatedFromAdminPage(
				$postID
			);
		}
	}
}

$IMWPLinkerLiteAdminProductSingle = new IMWPLinkerLiteAdminProduct();
add_action(
	'woocommerce_product_options_related',
	array($IMWPLinkerLiteAdminProductSingle, 'selectUpsellRelated')
);

add_action(
	'woocommerce_process_product_meta',
	array($IMWPLinkerLiteAdminProductSingle, 'saveUpsellRelated'),
	10,
	2
);