<?php
/**
 * Outputs the start wrapper HTML when editing a Term Group
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="postbox-container-1" class="postbox-container-1">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<!-- Actions -->
				<div id="page-generator-pro-actions" class="postbox">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">
							<span><?php esc_html_e( 'Actions', 'page-generator-pro' ); ?></span>
						</h2>
					</div>

					<div class="inside">
						<?php
						// Append to element IDs.
						$bottom = '';
						require $this->base->plugin->folder . '/views/admin/generate-meta-box-actions.php';
						?>
					</div>
				</div>

				<!-- Generation -->
				<div id="page-generator-pro-generation" class="postbox">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">
							<span><?php esc_html_e( 'Generation', 'page-generator-pro' ); ?></span>
						</h2>
					</div>

					<div class="inside">
						<?php
						$overwrite_documentation_url = $this->base->plugin->documentation_url . '/generate-terms/#fields--generation--overwrite';
						require $this->base->plugin->folder . '/views/admin/generate-meta-box-generation.php';
						?>
					</div>
				</div>

				<!-- Actions -->
				<div id="page-generator-pro-actions-bottom" class="postbox">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">
							<span><?php esc_html_e( 'Actions', 'page-generator-pro' ); ?></span>
						</h2>
					</div>

					<div class="inside">
						<?php
						// Append to element IDs.
						$bottom = 'bottom';
						require $this->base->plugin->folder . '/views/admin/generate-meta-box-actions.php';
						?>
					</div>
				</div>
			</div>
		</div>

		<div id="postbox-container-2" class="postbox-container-2">
			<!-- Term -->
			<div id="page-generator-pro-term" class="postbox">
				<div class="postbox-header">
					<h2 class="hndle ui-sortable-handle">
						<span><?php esc_html_e( 'Term', 'page-generator-pro' ); ?></span>
					</h2>
				</div>

				<div class="inside">
