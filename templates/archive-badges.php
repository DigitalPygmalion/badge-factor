<section class="profile-organisation-badges">
    <div class="wrap container" role="document">
        <div class="content row">
            <div class="profile-organisation-badges-heading col-xs-12">
                <span class="separator-prefix"></span><span class="separator-prefix"></span>
                <h3 class="profile-members-badges-heading-title"><?php _e('All badges', 'badgefactor'); ?> <small class="profile-members-badges-available"><?php echo $GLOBALS['badgefactor']->get_nb_badges(); ?> <?php _e('available', 'badgefactor'); ?></small></h3>
                <select name="organization-filter">
                    <option><?php _e('Filter by organization', 'badgefactor'); ?></option>
                    <?php foreach ($GLOBALS['badgefactor']->get_badge_organisations() as $organisation): ?>
                    <option value="<?php echo get_permalink($organisation->ID); ?>"><?php echo $organisation->post_title; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="category-filter">
                    <option><?php _e('Filter by category', 'badgefactor'); ?> -- TODO</option>
                </select>
            </div>
        </div>
        <div class="row badges-list">
            <?php foreach ($GLOBALS['badgefactor']->get_badges() as $badge): ?>
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