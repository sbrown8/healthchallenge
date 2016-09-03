<?php
/**
 * Template Name: Points
 *
 * @package Square Child
 */

get_header(); ?>

<header class="sq-main-header">
	<div class="sq-container">
		<?php the_title( '<h1 class="sq-main-title">', '</h1>' ); ?>
	</div>
</header><!-- .entry-header -->

<div class="sq-container">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'template-parts/content', 'page' ); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
				?>

			<?php endwhile; // End of the loop. ?>
			

		</main><!-- #main -->
	</div><!-- #primary -->
<script>
jQuery(document).ready(function() {
		var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	jQuery.post(
		ajaxurl,
		{
			'action'	: 'hc_date_changed_get_points',
			'data'		: jQuery("#ninja_forms_field_11").val()
		},
		function(results){
			var row = JSON.parse(results);
			if(row == null){
				row = {
					workout:0,
					cardio:0,
					weight_training:0,
					breakfast:0,
					lunch:0,
					dinner:0,
					water:0,
					vegetable:0,
					greens:0,
					sweets:0,
					junk:0,
					alcohol:0,
				};
			}
					
			jQuery('#ninja_forms_field_13').val(row.workout);
			jQuery('#ninja_forms_field_14').val(row.cardio);
			jQuery('#ninja_forms_field_18').val(row.weight_training);
			jQuery('#ninja_forms_field_19').val(row.water);
			if(row.breakfast == 1){
				jQuery('#ninja_forms_field_20').prop("checked", true);
			} else{
				jQuery('#ninja_forms_field_20').prop("checked", false);
			}
			if(row.lunch == 1){
				jQuery('#ninja_forms_field_21').prop("checked", true);
			} else{
				jQuery('#ninja_forms_field_21').prop("checked", false);
			}
			if(row.dinner == 1){
				jQuery('#ninja_forms_field_22').prop("checked", true);
			} else{
				jQuery('#ninja_forms_field_22').prop("checked", false);
			}	
			jQuery('#ninja_forms_field_23').val(row.vegetable);
			jQuery('#ninja_forms_field_24').val(row.greens);
			jQuery('#ninja_forms_field_25').val(row.sweets);
			jQuery('#ninja_forms_field_26').val(row.junk);
			jQuery('#ninja_forms_field_27').val(row.alcohol);
		}
		
	);
	

jQuery("#ninja_forms_field_11").change(function() {
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	jQuery.post(
		ajaxurl,
		{
			'action'	: 'hc_date_changed_get_points',
			'data'		: jQuery("#ninja_forms_field_11").val()
		},
		function(results){
			var row = JSON.parse(results);
			if(row == null){
				row = {
					workout:0,
					cardio:0,
					weight_training:0,
					breakfast:0,
					lunch:0,
					dinner:0,
					water:0,
					vegetable:0,
					greens:0,
					sweets:0,
					junk:0,
					alcohol:0,
				};
			}
					
			jQuery('#ninja_forms_field_13').val(row.workout);
			jQuery('#ninja_forms_field_14').val(row.cardio);
			jQuery('#ninja_forms_field_18').val(row.weight_training);
			jQuery('#ninja_forms_field_19').val(row.water);
			if(row.breakfast == 1){
				jQuery('#ninja_forms_field_20').prop("checked", true);
			} else{
				jQuery('#ninja_forms_field_20').prop("checked", false);
			}
			if(row.lunch == 1){
				jQuery('#ninja_forms_field_21').prop("checked", true);
			} else{
				jQuery('#ninja_forms_field_21').prop("checked", false);
			}
			if(row.dinner == 1){
				jQuery('#ninja_forms_field_22').prop("checked", true);
			} else{
				jQuery('#ninja_forms_field_22').prop("checked", false);
			}	
			jQuery('#ninja_forms_field_23').val(row.vegetable);
			jQuery('#ninja_forms_field_24').val(row.greens);
			jQuery('#ninja_forms_field_25').val(row.sweets);
			jQuery('#ninja_forms_field_26').val(row.junk);
			jQuery('#ninja_forms_field_27').val(row.alcohol);
		}
		
	);
}
);
});
</script>
<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>