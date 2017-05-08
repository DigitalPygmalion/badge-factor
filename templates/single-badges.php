<?php $member = isset($wp_query->query_vars['member']) ? $wp_query->query_vars['member'] : null; ?>
<section class="profile-organisation-badges">
    <div id="post-<?php echo $post->ID; ?>" <?php post_class(); ?>>
        <div class="container single-badge">
            <div class="row">
                <section class="badge-summary">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 badge-image-wrapper" style="text-align:center;">
                        <?php echo get_the_post_thumbnail($post, 'full'); ?>
                        <div style="margin-top:15px;">
                            <h4><?php _e('Issued by', 'badgefactor'); ?></h4>
                            <a href="<?php echo $GLOBALS['badgefactor']->get_badge_issuer_url($post->ID); ?>" style="text-decoration:none; font-size:15px;"><?php echo $GLOBALS['badgefactor']->get_badge_issuer_name($post->ID); ?></a>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
                        <h3 class="badge-description-heading">
                            <?php _e('Description', 'badgefactor'); ?>
                            <a class="btn btn-default add-badge" href="<?php echo $GLOBALS['badgefactor']->get_badge_page_url($post->ID); ?>"><?php _e('Take this course', 'badgefactor'); ?></a>
                        </h3>
                        <?php echo wpautop($post->post_content); ?>

                        <h3 class="badge-criteria-heading"><?php echo $GLOBALS['badgefactor']->get_badge_criteria_title($post->ID); ?></h3>
                        <?php echo wpautop($GLOBALS['badgefactor']->get_badge_criteria($post->ID)); ?>
                    </div>
                </section>
            </div>
            <div class="row">
                <section class="badge-members">
                    <div class="badge-members-heading col-xs-12">
                        <span class="separator-prefix"></span>
                        <h3 class="badges-unique-members-heading-title">
                            <?php _e('Members who obtained this badge', 'badgefactor'); ?> <small class="badge-members-count"><?php echo $GLOBALS['badgefactor']->get_nb_badge_earners($post->ID); ?> <?php _e('People', 'badgefactor'); ?></small>
                        </h3>
                    </div>
                    <?php if ($GLOBALS['badgefactor']->get_nb_badge_earners($post->ID) === 0): ?>
                        <div class="badge-member col-xs-12">
                            <?php _e('No one has earned this badge for the moment.', 'badgefactor'); ?>
                        </div>
                    <?php else: ?>

                        <ul class="badges-unique-members-list">

                            <?php foreach ($GLOBALS['badgefactor']->get_badge_earners($post->ID) as $member): ?>

                                <li class="badges-unique-members-single">
                                    <figure class="badges-unique-members-single-figure">
                                        <a href="<?php echo bp_core_get_user_domain($member->ID); ?>" class="badges-unique-members-single-link">
                                            <?php echo get_avatar($member->ID, 140); ?>
                                        </a>
                                        <figcaption class="badges-unique-members-single-details">
                                            <a href="<?php echo bp_core_get_user_domain($member->ID); ?>" class="badges-unique-members-single-link">
                                                <strong class="badges-unique-members-single-description">
                                                    <?php echo bp_core_get_user_displayname($member->ID); ?>
                                                </strong>
                                            </a>
                                        </figcaption>
                                    </figure>
                                </li>
                            <?php endforeach; ?>

                        </ul>

                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>