</div>
</div> <!-- megaoptim-cotnainer -->


<?php
$links = array(
	array(
		'name'  => __( 'Manage your support', 'megaoptim-image-optimizer' ),
		'title' => __( 'Place where you can manage your MegaOptim account, api key and subscription.' ),
		'link'  => 'https://app.megaoptim.com/',
	),
	array(
		'name'  => __( 'Technical support', 'megaoptim-image-optimizer' ),
		'title' => __( 'Place where you can manage your MegaOptim account, api key and subscription.', 'megaoptim-image-optimizer' ),
		'link'  => 'https://wordpress.org/support/plugin/megaoptim-image-optimizer/',
	),
	array(
		'name'  => __( 'How to use WP-CLI', 'megaoptim-image-optimizer' ),
		'title' => __( 'Learn how to use MegaOptim WP-CLI commands to make your life easier with the command line.', 'megaoptim-image-optimizer' ),
		'link'  => 'https://megaoptim.com/blog/how-to-optimize-wordpress-images-with-wp-cli-and-megaoptim/',
	),
);
?>

<div class="megaoptim-sidebar">
    <div class="megaoptim-widget">
        <div class="megaoptim-widget-title">
            <h3><?php _e( 'Resources', 'megaoptim-image-optimizer' ); ?></h3>
        </div>
        <div class="megaoptim-widget-content">
            <div class="megaoptim-widget-resource">
                <h4><?php _e( 'Some useful links', 'megaoptim-image-optimizer' ); ?></h4>
                <ul>
					<?php
					foreach ( $links as $link ) {
						echo sprintf( '<li><a href="%s" title="%s" target="_blank">%s</a></li>', $link['link'], $link['title'], $link['name'] );
					}
					?>
                </ul>
            </div>
            <div class="megaoptim-widget-resource">
                <h4><?php _e( 'Liked MegaOptim?', 'megaoptim-image-optimizer' ); ?></h4>
                <p><?php _e( 'Give us <a target="_blank" href="https://wordpress.org/support/plugin/megaoptim-image-optimizer/reviews/#new-post">5 star rating</a> and <a target="_blank" href="https://megaoptim.com/claim-free-tokens/">claim your <strong>1500 tokens for free</strong></a>', 'megaoptim-image-optimizer' ); ?></p>
            </div>
        </div>
    </div>

</div>


</div> <!-- .wrap.megaoptim -->