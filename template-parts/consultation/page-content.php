<?php
/**
 * Optional WordPress page content block above the form.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<section class="section-padding trimvia-consult-intro">
	<div class="container">
		<div class="row">
			<div class="col-12 trimvia-consult-intro__inner">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
</section>
