<?php
    $badges = $GLOBALS['badgefactor']->get_badge_page_badges();
?>

<section class="profile-organisation-badges">
    <div class="wrap container" role="document">
        <div class="content row">
            <div class="profile-organisation-badges-heading col-xs-12">
                <span class="separator-prefix"></span><span class="separator-prefix"></span>
                <h3 class="profile-members-badges-heading-title"><?php _e('All badges', 'badgefactor'); ?> <small class="profile-members-badges-available"><?php echo count($badges); ?> <?php _e('available', 'badgefactor'); ?></small></h3>

                <ul class="profile-members-badges-cta">
                    <li class="profile-members-badges-cta-item">
                        <button type="button" class="profile-members-badges-cta-item-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                        <?php _e('Filter by organization', 'badgefactor'); ?> <i class="profile-members-badges-cta-item-btn-caret" style="right:0px;"></i>
                        </button>
                        <ul class="profile-members-badges-cta-item-dropdown">
	                        <?php foreach ($GLOBALS['badgefactor']->get_badge_organisations() as $organisation): ?>
                                <li><a href="<?php echo get_permalink($organisation->ID); ?>"><?php echo $organisation->post_title; ?></a></li>
	                        <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>

                <ul class="profile-members-badges-cta">
                    <li class="profile-members-badges-cta-item">
                        <button type="button" class="profile-members-badges-cta-item-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Filtrer par catégorie				<i class="profile-members-badges-cta-item-btn-caret" style="right:0px;"></i>
                        </button>
                        <ul class="profile-members-badges-cta-item-dropdown">
		                    <?php $terms = get_terms(['product_cat'], ['hide_empty' => true]); ?>
		                    <?php $current_term = get_queried_object(); ?>
		                    <?php if (!empty($current_term) && is_a($current_term, 'WP_Term')) { ?>
                                <li><a href="<?php get_permalink(1504); ?>" > <?php _e('All', 'badgefactor'); ?> </a></li>

		                    <?php } ?>
		                    <?php foreach($terms as $term): ?>
			                    <?php $selected = !empty($current_term->slug) && $term->slug == $current_term->slug ? '1' : '0'; ?>
                                <li><a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a></li>
		                    <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row badges-list">
            <?php foreach ($badges as $badge): ?>
            <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                <div class="<?php post_class(); ?>">
                    <figure class="badges-figure">
                        <a href="<?php echo get_permalink($badge->ID); ?>" class="badges-link">
                            <?php echo get_the_post_thumbnail($badge, 'thumbnail', ['alt' => $badge->post_title] ); ?>
                        </a>
                        <figcaption class="badges-title">
                            <a href="<?php echo get_permalink($badge->ID); ?>" class="badges-link">
                                <span class="badge-title">
                                    <?php echo $badge->post_title; ?>
                                </span>
                            </a>
                        </figcaption>
                    </figure>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>