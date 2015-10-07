<?php
/**
 * Archive Template
 *
 * The archive template is basically a placeholder for archives that don't have a template file.
 * Ideally, all archives would be handled by a more appropriate template according to the current
 * page context.
 *
 * @package Hybrid
 * @subpackage Template
 */

get_header(); ?>

<script src="<?php echo UBCCART_PLUGIN_URI .'/assets/js/isotope.pkgd.min.js'; ?>"></script>
<link rel="stylesheet" href="<?php echo UBCCART_PLUGIN_URI .'/assets/css/demos.css'; ?>" />
<link rel="stylesheet" href="<?php echo UBCCART_PLUGIN_URI .'/assets/css/layout.css'; ?>" />

<script>
	var $container = jQuery('#isocontainer').isotope({itemSelector: '.element-item',layoutMode: 'fitRows'});
	function filterclick(obj){
		jQuery('#mfilters button').removeClass('active'); 
		jQuery(obj).addClass('active');
		var filterValue = jQuery(obj).attr('data-filter');
		jQuery('#iso-container').isotope({ filter: filterValue });
	}
</script>

	<div id="content" class="hfeed content span8<?php //echo apply_filters( 'ubc_collab_content_class', $content_class, 'ubc_product_archive' ); ?>">
		<?php //do_atomic( 'before_content' ); // Before content hook ?>
		<div class="archive-info hentry">
			<h1 class="archive-title"><?php _e( 'UBC Products', 'hybrid' ); ?></h1>
		</div><!-- .archive-info -->
<?php
//**********************************
//*    CART OPTIONS                *
//**********************************
$cartoptions = get_option( 'ubc_cart_options' );
$filter = '*';
$filter_id = $cartoptions['filter'];
$filter_term = get_term( $filter_id, 'ubc_product_type' );
$filter_option = $filter_term->slug;
$filter_name = $filter_term->name;
if ($filter_option) $filter = $filter_option;
?>
		<div id="mfilters">
			<button onclick="filterclick(this)" class="small cartbtn active" data-filter="*">show all</button>
			<?php
				$terms = get_terms( 'ubc_product_type' );
				foreach ( $terms as $term ) {
					if ( $term->slug == $filter_option ) {
						echo '<button  style="margin-left:5px;" onclick="filterclick(this)" class="cartbtn small filter" data-filter=".'.$term->slug.'">'.$term->name.'<span class="filtmark">*<span></button>';
					} else {
						echo '<button  style="margin-left:5px;" onclick="filterclick(this)" class="cartbtn small filter" data-filter=".'.$term->slug.'">'.$term->name.'</button>';
					}
				}
			?>
			<button  style="margin-left:5px;" onclick="window.location.href='<?php echo site_url('/checkout/'); ?>'" class="small cartbtn"><i class="icon-circle-arrow-right"></i> Go to Checkout</button>

		</div>
<?php if ( have_posts() ) : ?>

		<div id="iso-container">

		<?php
			global $query_string;
			query_posts( $query_string . '&posts_per_page=-1');
			while ( have_posts() ) : the_post();
				$terms_list = wp_get_post_terms($post->ID, 'ubc_product_type', array("fields" => "slugs") );
				$termstr = implode(" ",$terms_list);

		?>
			<div id="post-<?php the_ID(); ?>" data-category="all, <?php echo $termstr; ?>" class="isoitem element-item all <?php echo $termstr; ?>">

				<div class="product-summary" style="margin:auto;text-align:center;">
					<?php echo get_the_post_thumbnail($post->ID,'medium'); ?>
					<a style="text-decoration:none;" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><h3><?php the_title(); ?></h3></a>
					<?php //the_excerpt(); ?>
					<?php
						if ($filter_option){
							if (in_array($filter_option,$terms_list)){
								echo '<button class="cartbtn small pid_'.$post->ID.'" href="#"  onclick="addtocart(this,'.$post->ID.')"><i class="icon-shopping-cart"></i> Add to Cart</button>';
							}
							else{
								echo '<button class="cartbtn disabled small pid_'.$post->ID.'" href="#"  onclick=""><i class="icon-shopping-cart"></i> Add to Cart</button>';
							}
						}
						else{
							echo '<button class="cartbtn small pid_'.$post->ID.'" href="#"  onclick="addtocart(this,'.$post->ID.')"><i class="icon-shopping-cart"></i> Add to Cart</button>';
						}
					?>
				</div><!-- .entry-summary -->

			</div><!-- .hentry -->

			<?php endwhile; ?>
			</div> <!--iso-container-->

		<?php else: ?>

			<p class="no-data">
				<?php _e( 'Apologies, but no results were found.', 'hybrid' ); ?>
			</p><!-- .no-data -->

		<?php endif; ?>
		<?php //do_atomic( 'after_content' ); // After content hook ?>

	</div><!-- .content .hfeed -->

<?php get_footer(); ?>
