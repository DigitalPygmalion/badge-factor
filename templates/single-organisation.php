<?php get_header(); ?>

<div class="profile-organisation-intro">
    <?php echo wpautop($post->post_content); ?>
</div>

<section class="profile-members-badges">
    <div class="profile-members-badges-heading"><span class="separator-prefix"></span>
        <h3 class="profile-organisation-badges-heading-title">
            <?php _e('Available badges', 'badgefactor'); ?>
            <small class="profile-members-badges-available">
                <?php echo $GLOBALS['badgefactor']->get_nb_badges_by_organisation($post->ID); ?> <?php _e('available', 'badgefactor'); ?>
            </small>
        </h3>
        <ul class="profile-organisation-badges-cta">
            <li class="profile-members-badges-cta-item">
                <button type="button" class="profile-members-badges-cta-item-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php the_title(); ?>
                    <i class="profile-members-badges-cta-item-btn-caret"></i>
                </button>
                <ul class="profile-members-badges-cta-item-dropdown">
                    <li><a href="<?php echo get_permalink(1504);?>">Tous</a></li>
                    <?php foreach ($GLOBALS['badgefactor']->get_badge_organisations(true) as $organisation): ?>
                        <li><a href="<?php the_permalink($organisation->ID); ?>"><?php echo $organisation->post_title; ?></a></li>
                    <?php endforeach; ?>

                </ul>
            </li>
        </ul>

    </div>
    <ul class="profile-members-badges-list">

        <?php foreach ($GLOBALS['badgefactor']->get_badges_by_organisation($post->ID) as $product): ?>

            <li class="profile-members-badge">
                <figure class="profile-members-badge-figure">
                    <a href="<?php the_permalink($product->ID); ?>" class="profile-members-badge-link">
                        <img src="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id($product->ID), 'single-post-thumbnail')[0]; ?>" srcset="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id($product->ID), 'single-post-thumbnail')[0]; ?> 1x, <?php echo wp_get_attachment_image_src( get_post_thumbnail_id($product->ID), 'single-post-thumbnail')[0]; ?> 2x" class="profile-members-badge-image">
                    </a>
                    <figcaption class="profile-members-badge-details">
                    <span class="profile-members-badge-description">
                      	<?php
                        echo $product->post_title;
                        ?>
                    </span>
                    </figcaption>
                </figure>
            </li>

        <?php endforeach; ?>
    </ul>
</section>
