<?php
$member = isset($wp_query->query_vars['member']) ? get_user_by('slug', $wp_query->query_vars['member']) : null;
$uri_embed = preg_replace('/\/(\?.*)?$/', '/', $_SERVER['REQUEST_URI']);

$base_proto = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $base_proto .= 's';
}

$submission = $post;

$badge = $GLOBALS['badgefactor']->get_badge_by_submission($submission);

$badge_page_url = get_field('badgefactor_page_url', $badge->ID);

if (!$badge_page_url) {

    $badge_page_url = $GLOBALS['badgefactor']->get_badge_page_url($badge->ID);
}

$categories = get_the_terms($badge->ID, 'badge_category');

// Hide some informations when this page is embeded.
if (($member) and (preg_match('/\?embed/i', $_SERVER['REQUEST_URI']))){

echo '<style>.header-main, .page-main-heading, .footer-main{ display:none;}</style>';

?>
<div class="container">

    <section class="profile-organisation-badges">
        <div id="post-<?php echo $post->ID; ?>" <?php post_class(); ?>>
            <div class="container single-badge">
                <div class="row">
                    <section class="badge-summary">
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 badge-image-wrapper"
                             style="text-align:center;">
                            <?php echo get_the_post_thumbnail($post, 'medium'); ?>
                            <div style="margin-top:15px; font-size:14px; font-weight:bold;line-height:30px;">
                                <a href="<?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed; ?>"
                                   style="text-decoration:none;"
                                   target="_blank"><?php echo $badge->post_title; ?></a><br>
                                <a href="<?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed; ?>"
                                   style="text-decoration:none;"
                                   target="_blank"><?php echo $GLOBALS['badgefactor']->get_badge_issuer_name($badge->ID); ?></a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
    <?php
    exit;
    }

    // Print format for this page.
    if (($member) and (preg_match('/\?print/i', $_SERVER['REQUEST_URI']))){

    $image_size = "full";
    $col_lg_image = "col-lg-4";
    $col_lg_description = "col-lg-8";

    echo '<style>
              .header-main, .page-main-heading, .footer-main{ display:none;}
              .container { width: auto !important;}
              </style>';
    echo '<script type="text/javascript">window.print();</script>';

    ?>
    <div class="container">

        <section class="profile-organisation-badges">
            <div id="post-<?php echo $post->ID; ?>" <?php post_class(); ?>>
                <div class="container single-badge">
                    <div class="row">
                        <section class="badge-summary">
                            <div class="col-xs-12 col-sm-6 col-md-4 <?php echo $col_lg_image; ?> badge-image-wrapper"
                                 style="text-align:center;">
                                <?php echo get_the_post_thumbnail($post, $image_size); ?>
                                <div style="margin-top:15px;">
                                    <?php if ($member): ?>
                                        <span style="text-decoration:none; font-size:20px;"><?php echo $member->display_name; ?></span>
                                        <p style="text-align:center;"><?php _e('obtained this badge!', 'badgefactor'); ?></p>
                                    <?php endif; ?>
                                    <h4><?php _e('Issued by', 'badgefactor'); ?></h4>
                                    <span style="text-decoration:none; font-size:15px;"><?php echo $GLOBALS['badgefactor']->get_badge_issuer_name($badge->ID); ?></span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-8 <?php echo $col_lg_description; ?>">
                                <h3 class="badge-description-heading">
                                    <?php _e('Description', 'badgefactor'); ?>
                                </h3>
                                <?php echo wpautop($badge->post_content); ?>

                                <?php if (!empty($categories)) { ?>
                                    <h3 class="badge-criteria-heading"><?php _e('Category', 'badgefactor'); ?></h3>
                                    <?php echo '<ul><li>' . esc_html($categories[0]->name) . '</li></ul>';
                                } ?>

                                <?php if ($GLOBALS['badgefactor']->badge_has_criteria($badge->ID)): ?>
                                    <h3 class="badge-criteria-heading"><?php echo $GLOBALS['badgefactor']->get_badge_criteria_title($badge->ID); ?></h3>
                                    <?php echo wpautop($GLOBALS['badgefactor']->get_badge_criteria($badge->ID)); ?>
                                <?php endif; ?>

                                <?php if ($member): ?>
                                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6" style="padding-left:0;">
                                        <h3 class="badge-date-heading"><?php _e('Issued on', 'badgefactor'); ?></h3>
                                        <span class="badges-unique-granted-date"><?php echo date_i18n('d F Y', strtotime($the_submission->post_modified)); ?></span>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                        <?php echo $certificate_url; ?>
                                    </div>

                                    <div class="clearfix block-social-networks" style="clear:both;">
                                        URL: <?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed; ?>
                                    </div>

                                <?php endif; ?>
                            </div>

                        </section>
                    </div>

                </div>
            </div>
        </section>

        <?php
        exit;
        }

        if ($member) {

            $the_submission = $GLOBALS['badgefactor']->get_submission($submission->ID, $member);

            if ($the_submission->post_type == "nomination") {
                $proof = get_post_meta($the_submission->ID, "_badgeos_nomination_preuve", true);
                if ($proof) {
                    $certificate_proof = '<a class="btn get-proof badges-unique-granted-link" href="' . $proof . '">' . __('Get Proof', 'badgefactor') . '</a>';
                }
            } else {
                // If we find 'Submitted Form' in it, it's from Gravityform
                if (preg_match('/Submitted Form/i', $the_submission->post_content)) {

                    $dom = new DOMDocument();
                    @$dom->loadHTML($the_submission->post_content);

                    $a = $dom->getElementsByTagName('a');
                    $i = 0;
                    for ($i; $i < $a->length; $i++) {
                        $certificate_link = $a->item($i)->getAttribute('href');
                        $certificate_link = $base_proto . '://' . $_SERVER['HTTP_HOST'] . rtrim($a->item($i)->getAttribute('href'), '/') . '/';
                    }

                    $certificate_proof = '<a class="btn get-proof badges-unique-granted-link" href="' . $certificate_link . '">' . __('Get Proof', 'badgefactor') . '</a>';

                }
            }

            $certificate_link = $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed . 'certificate/';
            $certificate_bouton = '<a class="btn badges-unique-granted-link" href="' . $certificate_link . '">' . __('Download', 'badgefactor') . '</a>';


            $image_size = "full";
            $col_lg_image = "col-lg-4";
            $col_lg_description = "col-lg-8";


        } else {

            $image_size = "medium";
            $col_lg_image = "col-lg-3";
            $col_lg_description = "col-lg-6";
        }


        // $proof = $GLOBALS['badgefactor']->get_proof($submission->ID);
        ?>
        <div class="container">

            <section class="profile-organisation-badges">
                <div id="post-<?php echo $post->ID; ?>" <?php post_class(); ?>>
                    <div class="container single-badge">
                        <div class="row">
                            <section class="badge-summary">
                                <div class="col-xs-12 col-sm-6 col-md-4 <?php echo $col_lg_image; ?> badge-image-wrapper"
                                     style="text-align:center;">
                                    <?php echo get_the_post_thumbnail($post, $image_size); ?>
                                    <div style="margin-top:15px;">
                                        <?php if ($member): ?>
                                            <a href="<?php echo bp_core_get_user_domain($member->ID); ?>"
                                               style="text-decoration:none; font-size:20px;"><?php echo $member->display_name; ?></a>
                                            <p style="text-align:center;"><?php _e('obtained this badge!', 'badgefactor'); ?></p>
                                        <?php endif; ?>
                                        <h4><?php _e('Issued by', 'badgefactor'); ?></h4>
                                        <a href="<?php echo $GLOBALS['badgefactor']->get_badge_issuer_url($badge->ID); ?>"
                                           style="text-decoration:none; font-size:15px;"><?php echo $GLOBALS['badgefactor']->get_badge_issuer_name($badge->ID); ?></a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-8 <?php echo $col_lg_description; ?>">
                                    <h3 class="badge-description-heading">
                                        <?php _e('Description', 'badgefactor'); ?>
                                    </h3>
                                    <?php echo wpautop($badge->post_content); ?>

                                    <?php if (!empty($categories)) { ?>
                                        <h3 class="badge-criteria-heading"><?php _e('Category', 'badgefactor'); ?></h3>
                                        <?php echo '<ul><li>' . esc_html($categories[0]->name) . '</li></ul>';
                                    } ?>

                                    <?php if ($GLOBALS['badgefactor']->badge_has_criteria($badge->ID)): ?>
                                        <h3 class="badge-criteria-heading"><?php echo $GLOBALS['badgefactor']->get_badge_criteria_title($badge->ID); ?></h3>
                                        <?php echo wpautop($GLOBALS['badgefactor']->get_badge_criteria($badge->ID)); ?>
                                    <?php endif; ?>

                                    <?php if ($member): ?>
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="padding-left:0;">
                                            <h3 class="badge-date-heading"><?php _e('Issued on', 'badgefactor'); ?></h3>
                                            <span class="badges-unique-granted-date"><?php echo date_i18n('d F Y', strtotime($the_submission->post_modified)); ?></span>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="padding-left:0;">
                                            <?php
                                            $type_fin = get_post_meta($submission->ID, "type_fin", true);
                                            if ($type_fin == "end_date") {
                                                if ($date_fin = get_post_meta($submission->ID, "end_date", true)) {
                                                    ?>
                                                    <h3 class="badge-date-heading"><?php _e('Date de fin', 'badgefactor'); ?></h3>
                                                    <span class="badges-unique-granted-date"><?php echo date_i18n('d F Y', strtotime($date_fin)); ?></span>
                                                <?php } ?>
                                            <?php } elseif ($type_fin == 'duree_fin') {
                                                if ($duree_fin = get_post_meta($submission->ID, "duree_fin", true)) {
                                                ?>
                                                    <h3 class="badge-date-heading"><?php _e('Date de fin', 'badgefactor'); ?></h3>
                                                    <span class="badges-unique-granted-date"><?php echo date_i18n('d F Y', strtotime($the_submission->post_modified) + ($duree_fin * YEAR_IN_SECONDS ) ); ?></span>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                            <?php echo $certificate_proof; ?>
                                        </div>

                                        <div class="clearfix block-social-networks" style="clear:both;">
                                            <?php if (get_option("badge_partage_public") || $member->ID === wp_get_current_user()->ID): ?>
                                                <p>
                                                <h3 class="badges-unique-content-heading"><?php _e('Share badge', 'badgefactor'); ?></h3>
                                                </p>
                                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3"
                                                     style="padding-left:0;">
                                                    <p>&nbsp;</p>
                                                    <?php _e('Spread', 'badgefactor'); ?><br>
                                                    <div class="share-buttons">
                                                        <div class="fb-share-button"
                                                             data-href="<?php echo $_SERVER['HTTP_HOST'] . $uri_embed; ?>"
                                                             data-layout="button_count">
                                                        </div>
                                                        <iframe
                                                                src="https://platform.twitter.com/widgets/tweet_button.html?size=l&url=<?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed; ?>&text=J'ai%20obtenu%20un%20badge!"
                                                                width="140"
                                                                height="40"
                                                                title="Twitter Tweet Button"
                                                                style="border: 0 !important; overflow: hidden !important;">
                                                        </iframe>
                                                        <br>
                                                        <iframe src="https://www.facebook.com/plugins/share_button.php?href=<?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed; ?>&layout=button&size=large&mobile_iframe=true&appId=24077487353&width=95&height=28"
                                                                width="95" height="28"
                                                                style="border:none;overflow:hidden;margin-bottom:12px;"
                                                                scrolling="no" frameborder="0"
                                                                allowTransparency="true"></iframe>
                                                        <br>
                                                        <script src="//platform.linkedin.com/in.js"
                                                                type="text/javascript"> lang: fr_FR</script>
                                                        <script type="IN/Share"></script>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3"
                                                     style="padding-left:0;">
                                                    <p>&nbsp;</p>
                                                    <?php _e('Integrate', 'badgefactor'); ?><br>
                                                    <button id="copyClipboard"
                                                            class='btn badges-unique-granted-link'><?php _e('Copy code', 'badgefactor'); ?></button>
                                                    <input type="hidden" id="iframe"
                                                           value="<iframe style='overflow:hidden;' scrolling='no' width='350' height='450' src='<?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed . '?embed'; ?>' frameborder='0' allowfullscreen></iframe>">

                                                </div>

                                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3"
                                                     style="padding-left:0;">
                                                    <p>&nbsp;</p>
                                                    <?php _e('Print', 'badgefactor'); ?><br>
                                                    <a class="btn badges-unique-granted-link"
                                                       href="<?php echo $base_proto . '://' . $_SERVER['HTTP_HOST'] . $uri_embed . '?print'; ?>"><?php echo __('Print badge', 'badgefactor') ?></a>
                                                </div>

                                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3"
                                                     style="padding-left:0;">
                                                    <p>&nbsp;</p>
                                                    <?php _e('Certificate', 'badgefactor'); ?><br>
                                                    <?php echo $certificate_bouton; ?>
                                                </div>

                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!$member): ?>
                                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 badge-image-wrapper"
                                         style="text-align:center;">
                                        <a class="btn badges-unique-granted-link add-badge"
                                           href="<?php echo $badge_page_url; ?>">
                                            <?php if($titre_more_info = get_post_meta($badge->ID,"badgefactor_page_title",true)){ echo $titre_more_info; }else{ _e('More information<br>and register', 'badgefactor') ; } ?></a>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <?php if (!$member): ?>
                            <div class="row">
                                <section class="badge-members">
                                    <div class="badge-members-heading col-xs-12">
                                        <span class="separator-prefix"></span>
                                        <h3 class="badges-unique-members-heading-title">
                                            <?php _e('Members who obtained this badge', 'badgefactor'); ?>
                                            <small class="badge-members-count"><?php echo $GLOBALS['badgefactor']->get_nb_badge_earners($badge->ID); ?> <?php _e('People', 'badgefactor'); ?></small>
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
                                                        <a href="<?php echo bp_core_get_user_domain($member->ID); ?>"
                                                           class="badges-unique-members-single-link">
                                                            <?php echo get_avatar($member->ID, 140); ?>
                                                        </a>
                                                        <figcaption class="badges-unique-members-single-details">
                                                            <a href="<?php echo bp_core_get_user_domain($member->ID); ?>"
                                                               class="badges-unique-members-single-link">
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
