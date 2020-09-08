<?php
/**
 * Outputs the Edit Gallery panel with navigation.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Gallery;
use WoowGallery\Skins;

/**
 * Template vars
 *
 * @var array $data = [
 *              post,
 *              gallery,
 *              tabs,
 *              settings,
 *            ]
 */

$screen                 = get_current_screen();
$meta_gallery_skin_slug = get_metadata( 'post', $data['post']->ID, Gallery::GALLERY_SKIN_META_KEY, true );
$gallery_skin           = $data['gallery']['skin'];
$show_skins_block       = 'add' === $screen->action || $meta_gallery_skin_slug !== $gallery_skin['slug'];
?>
<div id="woowgallery" class="postbox-container woowgallery-wrap" style="padding-top: 20px;">
	<div class="postbox woowgallery-postbox">
		<div class="inside">

			<!-- Templates -->
			<div id="woowgallery-skin-select"<?php echo ! $show_skins_block ? ' class="closed" style="display:none;"' : ''; ?>>
				<div class="woowgallery-skins">
					<?php
					$skins = Skins::get_instance()->get_skins();
					// Iterate through the available templates, outputting them in a list.
					foreach ( $skins as $slug => $skin ) {
						$info = $skin->info;
						?>
						<div class="woowgallery-skin woowgallery_skin_<?php echo esc_attr( $slug ); ?>">
							<label for="woowgallery_skin_<?php echo esc_attr( $slug ); ?>">
								<input type="radio" id="woowgallery_skin_<?php echo esc_attr( $slug ); ?>" name="_woowgallery[skin]" value="<?php echo esc_attr( $slug ); ?>"<?php checked( $gallery_skin['slug'], $slug ); ?> />
								<img src="<?php echo esc_url( $info['screenshots'][0] ); ?>" alt="<?php echo esc_attr( $info['name'] ); ?>"/>
								<span class="skin-info"><span class="skin-title"><?php echo esc_html( $info['name'] ); ?></span> v<?php echo esc_html( $info['version'] ); ?></span>
							</label>
							<?php
							if ( ! empty( $info['premium'] ) ) {
								woowgallery_is_premium_feature();
							}
							?>
						</div>
						<?php
					}
					if ( $gallery_skin['slug'] ) {
						// Set Gallery's skin model.
						$skins[ $gallery_skin['slug'] ]->model['_custom'] = $gallery_skin['config'];
					}
					?>
				</div>
			</div>

			<!-- Top Buttons -->
			<div class="woowgallery-top-buttons">
				<div class="wg-media-buttons wg-clearfix">
					<?php do_action( 'woowgallery_media_buttons', $data['post'] ); ?>
				</div>
				<div id="activity" class="woowgallery-choose-skin<?php echo ! $show_skins_block ? ' closed' : ''; ?>">
					<button type="button" class="button button-secondary handleskinsdiv">
						<span class="dashicons dashicons-admin-appearance" title="<?php esc_attr_e( 'Choose Skin', 'woowgallery' ); ?>"></span>
					</button>
				</div>
			</div>

			<div class="woowgallery-flex align-top">
				<!-- Tabs -->
				<ul id="woowgallery-tabs-nav" class="woowgallery-tabs-nav" data-container="#woowgallery-tabs">
					<?php
					// Iterate through the available tabs, outputting them in a list.
					$i = 0;
					foreach ( $data['tabs'] as $tab_id => $tab_info ) {
						$class = ( 0 === $i ? 'woowgallery-active' : '' );
						?>
						<li class="woowgallery-tab-nav-<?php echo esc_attr( $tab_id ); ?>">
							<a href="#woowgallery-tab-<?php echo esc_attr( $tab_id ); ?>" title="<?php echo esc_attr( $tab_info['label'] ); ?>" class="<?php echo esc_attr( $class ); ?>">
								<span class="dashicons <?php echo esc_attr( $tab_info['icon'] ); ?>"></span>
								<span class="tab-label"><?php echo esc_html( $tab_info['label'] ); ?></span>
							</a>
						</li>
						<?php
						$i ++;
					}
					?>
				</ul>

				<!-- Settings -->
				<div id="woowgallery-tabs" data-navigation="#woowgallery-tabs-nav">
					<?php
					// Iterate through the registered tabs, outputting a panel and calling a tab-specific action,
					// which renders the settings view for that tab.
					$i = 0;
					foreach ( $data['tabs'] as $tab_id => $tab_title ) {
						$class = ( 0 === $i ? ' woowgallery-active' : '' );
						?>
						<div id="woowgallery-tab-<?php echo esc_attr( $tab_id ); ?>" class="woowgallery-tab<?php echo esc_attr( $class ); ?>">
							<?php do_action( 'woowgallery_tab_' . $tab_id, $data['post'] ); ?>
						</div>
						<?php
						$i ++;
					}
					?>
				</div>
			</div>

		</div>
	</div>

	<?php
	wp_nonce_field( 'gallery_save', '_nonce_woowgallery_gallery_save', false );
	?>
	<script><?php echo 'var woowgallery_skin = ' . wp_json_encode( $skins, JSON_FORCE_OBJECT ) . ';'; ?></script>
	<script><?php echo 'var woowgallery_content = ' . wp_json_encode( $data['gallery']['content'] ) . ';'; ?></script>
</div>
