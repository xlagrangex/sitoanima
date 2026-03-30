<?php
/**
 * Quick View Modal Shell (empty — populated via AJAX)
 *
 * @package BizStudio
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="biz-qv-overlay" id="biz-qv-overlay"></div>
<div class="biz-qv" id="biz-qv" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Anteprima prodotto', 'bizstudio' ); ?>">
	<button class="biz-qv__close" id="biz-qv-close" aria-label="<?php esc_attr_e( 'Chiudi', 'bizstudio' ); ?>">
		<?php echo bizstudio_icon( 'close' ); ?>
	</button>
	<div class="biz-qv__body" id="biz-qv-body">
		<div class="biz-quick-view__skeleton">
			<div class="biz-skeleton biz-skeleton--image"></div>
			<div>
				<div class="biz-skeleton biz-skeleton--text" style="width:60%"></div>
				<div class="biz-skeleton biz-skeleton--text-sm" style="width:40%"></div>
				<div class="biz-skeleton biz-skeleton--text" style="width:80%;margin-top:1rem"></div>
				<div class="biz-skeleton biz-skeleton--text" style="width:50%;margin-top:2rem"></div>
			</div>
		</div>
	</div>
</div>
