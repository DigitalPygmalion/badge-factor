<?php
    $member = isset($wp_query->query_vars['member']) ? get_user_by('login', $wp_query->query_vars['member']) : null;

    $submission = $post;
    $badge = $GLOBALS['badgefactor']->get_badge_by_submission($submission);

    $proof = $GLOBALS['badgefactor']->get_proof($submission->ID);

    $badgeProductId = get_field('badgefactor_product_id',$badge->ID);
?>

<section class="profile-organisation-badges">
    <div id="post-<?php echo $post->ID; ?>" <?php post_class(); ?>>
        <div class="container single-badge">
            <div class="row">
                <section class="badge-summary">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 badge-image-wrapper" style="text-align:center;">
                        <?php echo get_the_post_thumbnail($post, 'full'); ?>
                        <div style="margin-top:15px;">
                            <?php if ($member): ?>
                            <a href="<?php echo bp_core_get_user_domain($member->ID); ?>" style="text-decoration:none; font-size:20px;"><?php echo $member->display_name; ?></a>
                            <p style="text-align:center;"><?php _e('obtained this badge!', 'badgefactor'); ?></p>
                            <?php endif; ?>
                            <h4><?php _e('Issued by', 'badgefactor'); ?></h4>
                            <a href="<?php echo $GLOBALS['badgefactor']->get_badge_issuer_url($badge->ID); ?>" style="text-decoration:none; font-size:15px;"><?php echo $GLOBALS['badgefactor']->get_badge_issuer_name($badge->ID); ?></a>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
                        <h3 class="badge-description-heading">
                            <?php _e('Description', 'badgefactor'); ?>
                            <?php if (!$member): ?>
                                <?php if ($GLOBALS['badgefactorwcaddon']): ?>
                                    <a class="btn btn-success" href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ) ?>?add-to-cart=<?php echo $badgeProductId ?>"><?php _e('Add to cart', 'badgefactor'); ?></a>
                                <?php else: ?>
                                    <a class="btn btn-default add-badge" href="<?php echo $GLOBALS['badgefactor']->get_badge_page_url($badge->ID); ?>"><?php _e('Take this course', 'badgefactor'); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </h3>
                        <?php echo wpautop($badge->post_content); ?>

	                    <?php if ($GLOBALS['badgefactor']->badge_has_criteria($badge->ID)): ?>
                        <h3 class="badge-criteria-heading"><?php echo $GLOBALS['badgefactor']->get_badge_criteria_title($badge->ID); ?></h3>
                        <?php echo wpautop($GLOBALS['badgefactor']->get_badge_criteria($badge->ID)); ?>
                        <?php endif; ?>

                        <?php if ($member): ?>
                        <h3 class="badge-date-heading"><?php _e('Issued on', 'badgefactor'); ?></h3>
                        <span class="badges-unique-granted-date"><?php echo date_i18n('d F Y', strtotime($submission->post_modified)); ?></span>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
	        <?php if (!$member): ?>
                <div class="row">
                    <section class="badge-members">
                        <div class="badge-members-heading col-xs-12">
                            <span class="separator-prefix"></span>
                            <h3 class="badges-unique-members-heading-title">
						        <?php _e('Members who obtained this badge', 'badgefactor'); ?> <small class="badge-members-count"><?php echo $GLOBALS['badgefactor']->get_nb_badge_earners($badge->ID); ?> <?php _e('People', 'badgefactor'); ?></small>
                            </h3>
                        </div>
				        <?php if ($GLOBALS['badgefactor']->get_nb_badge_earners($badge->ID) === 0): ?>
                            <div class="badge-member col-xs-12">
						        <?php _e('No one has earned this badge for the moment.', 'badgefactor'); ?>
                            </div>
				        <?php else: ?>

                            <ul class="badges-unique-members-list">

						        <?php foreach ($GLOBALS['badgefactor']->get_badge_earners($badge->ID) as $member): ?>

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
	        <?php endif; ?>
        </div>
    </div>
</section>
