<?php

// --- Dashboard widget ---
add_action( 'wp_dashboard_setup', function() {
    wp_add_dashboard_widget( 'ufc_dashboard_overview', 'UFC Community — Vue d\'ensemble', 'ufc_dashboard_widget_content' );
} );

function ufc_dashboard_widget_content() {
    $post_count  = wp_count_posts()->publish;
    $user_count  = count_users()['total_users'];
    $event_count = 0;
    if ( function_exists( 'tribe_get_events' ) ) {
        $event_count = count( tribe_get_events( array( 'posts_per_page' => -1, 'start_date' => current_time( 'Y-m-d H:i:s' ), 'eventDisplay' => 'list' ) ) );
    }
    echo '<div style="display:flex;gap:20px;text-align:center;">';
    echo '<div style="flex:1;padding:15px;background:#1a1a1a;border-radius:8px;border-left:4px solid #d20a0a;"><h3 style="margin:0;color:#d20a0a;font-size:2em;">' . esc_html( $post_count ) . '</h3><p style="margin:5px 0 0;color:#999;">Articles</p></div>';
    echo '<div style="flex:1;padding:15px;background:#1a1a1a;border-radius:8px;border-left:4px solid #c59e5e;"><h3 style="margin:0;color:#c59e5e;font-size:2em;">' . esc_html( $event_count ) . '</h3><p style="margin:5px 0 0;color:#999;">Événements</p></div>';
    echo '<div style="flex:1;padding:15px;background:#1a1a1a;border-radius:8px;border-left:4px solid #28a745;"><h3 style="margin:0;color:#28a745;font-size:2em;">' . esc_html( $user_count ) . '</h3><p style="margin:5px 0 0;color:#999;">Membres</p></div>';
    echo '</div>';
}

add_action( 'admin_head', function() {
    echo '<style>#ufc_dashboard_overview .inside{background:#111;padding:20px;border-radius:8px}#ufc_dashboard_overview h2{background:#d20a0a;color:#fff;padding:10px 15px}</style>';
} );

// --- Meta box RSVP tiers (Or/Argent/Bronze) ---
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'ufc_rsvp_tiers', 'Configuration des Places UFC (Or / Argent / Bronze)', 'ufc_render_rsvp_tiers_meta_box', 'tribe_events', 'normal', 'high' );
} );

function ufc_render_rsvp_tiers_meta_box( $post ) {
    wp_nonce_field( 'ufc_rsvp_tiers_save', 'ufc_rsvp_tiers_nonce' );

    if ( ! class_exists( 'Tribe__Tickets__RSVP' ) ) {
        echo '<p style="color:#d20a0a;">Le plugin Event Tickets doit être activé.</p>';
        return;
    }

    $rsvp      = Tribe__Tickets__RSVP::get_instance();
    $existing  = get_posts( array( 'post_type' => $rsvp->ticket_object, 'posts_per_page' => 10, 'post_status' => 'any', 'meta_query' => array( array( 'key' => '_tribe_rsvp_for_event', 'value' => $post->ID ) ), 'orderby' => 'date', 'order' => 'ASC' ) );

    $tier_data = array();
    foreach ( $existing as $ticket ) {
        $level = get_post_meta( $ticket->ID, '_ufc_tier_level', true );
        if ( $level ) { $tier_data[ $level ] = $ticket; }
    }

    $tiers = array(
        'or'     => array( 'name' => 'Place Or — Cage Side',       'desc' => 'Place premium au plus près de l\'octogone. Vue imprenable, accès VIP, boissons offertes.', 'capacity' => 20,  'color' => '#c59e5e' ),
        'argent' => array( 'name' => 'Place Argent — Tribune Haute', 'desc' => 'Excellente visibilité depuis les tribunes hautes. Bon rapport qualité/vue.',               'capacity' => 50,  'color' => '#a0a0a0' ),
        'bronze' => array( 'name' => 'Place Bronze — Gradins',     'desc' => 'Place standard dans les gradins. Ambiance électrique à prix accessible.',                   'capacity' => 100, 'color' => '#cd7f32' ),
    );

    echo '<style>.ufc-tier-box{border:2px solid #ddd;border-radius:8px;padding:15px;margin-bottom:15px;background:#fff}.ufc-tier-box.active{border-color:var(--tier-color)}.ufc-tier-header{display:flex;align-items:center;gap:12px;margin-bottom:12px}.ufc-tier-header label{font-size:15px;font-weight:700;cursor:pointer}.ufc-tier-header input[type="checkbox"]{width:20px;height:20px;cursor:pointer}.ufc-tier-fields{display:grid;grid-template-columns:1fr 1fr;gap:10px}.ufc-tier-fields.hidden{display:none}.ufc-tier-field{display:flex;flex-direction:column;gap:4px}.ufc-tier-field.full{grid-column:1/-1}.ufc-tier-field label{font-size:12px;color:#666;text-transform:uppercase;letter-spacing:1px}.ufc-tier-field input,.ufc-tier-field textarea{background:#f9f9f9;border:1px solid #ccc;padding:8px 12px;border-radius:4px;font-size:14px}.ufc-tier-field textarea{min-height:60px;resize:vertical}.ufc-tier-status{font-size:12px;color:#888;margin-top:8px}.ufc-tier-status .exists{color:#28a745}.ufc-tier-status .new{color:#c59e5e}</style>';

    echo '<div style="background:#f0f0f0;border:1px solid #ccc;border-radius:6px;padding:12px;margin-bottom:15px;color:#444;font-size:13px"><strong style="color:#c59e5e">Configuration des places par événement</strong><br>Cochez les niveaux à proposer. Personnalisez nom, description et capacité.</div>';

    foreach ( $tiers as $key => $defaults ) {
        $ticket    = isset( $tier_data[ $key ] ) ? $tier_data[ $key ] : null;
        $is_active = ( $ticket !== null );
        $name      = $ticket ? $ticket->post_title : $defaults['name'];
        $desc      = $ticket ? $ticket->post_content : $defaults['desc'];
        $capacity  = $ticket ? get_post_meta( $ticket->ID, '_tribe_ticket_capacity', true ) : $defaults['capacity'];
        $sales     = $ticket ? get_post_meta( $ticket->ID, 'total_sales', true ) : '0';
        $k         = esc_attr( $key );

        echo '<div class="ufc-tier-box' . ( $is_active ? ' active' : '' ) . '" style="--tier-color:' . esc_attr( $defaults['color'] ) . '">';
        echo '<div class="ufc-tier-header">';
        echo '<input type="checkbox" name="ufc_tier[' . $k . '][active]" value="1" id="ufc_tier_' . $k . '" ' . checked( $is_active, true, false ) . ' onchange="this.closest(\'.ufc-tier-box\').querySelector(\'.ufc-tier-fields\').classList.toggle(\'hidden\',!this.checked);this.closest(\'.ufc-tier-box\').classList.toggle(\'active\',this.checked)">';
        echo '<label for="ufc_tier_' . $k . '">' . esc_html( $defaults['name'] ) . '</label></div>';
        echo '<div class="ufc-tier-fields' . ( $is_active ? '' : ' hidden' ) . '">';
        echo '<div class="ufc-tier-field"><label>Nom</label><input type="text" name="ufc_tier[' . $k . '][name]" value="' . esc_attr( $name ) . '"></div>';
        echo '<div class="ufc-tier-field"><label>Capacité</label><input type="number" name="ufc_tier[' . $k . '][capacity]" value="' . esc_attr( $capacity ) . '" min="1" max="10000"></div>';
        echo '<div class="ufc-tier-field full"><label>Description</label><textarea name="ufc_tier[' . $k . '][desc]">' . esc_textarea( $desc ) . '</textarea></div>';
        if ( $ticket ) {
            echo '<div class="ufc-tier-status"><span class="exists">✓ Existant</span> — ' . esc_html( $sales ) . ' réservation(s)</div>';
        } else {
            echo '<div class="ufc-tier-status"><span class="new">● Créé à la sauvegarde</span></div>';
        }
        echo '</div></div>';
    }
}

// --- Sauvegarde meta box RSVP ---
add_action( 'save_post_tribe_events', 'ufc_save_rsvp_tiers_meta_box', 20 );
function ufc_save_rsvp_tiers_meta_box( $post_id ) {
    if ( ! isset( $_POST['ufc_rsvp_tiers_nonce'] ) || ! wp_verify_nonce( $_POST['ufc_rsvp_tiers_nonce'], 'ufc_rsvp_tiers_save' ) ) { return; }
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
    if ( ! class_exists( 'Tribe__Tickets__RSVP' ) ) { return; }

    $rsvp      = Tribe__Tickets__RSVP::get_instance();
    $post_type = $rsvp->ticket_object;
    $tiers     = isset( $_POST['ufc_tier'] ) ? $_POST['ufc_tier'] : array();

    $existing = get_posts( array( 'post_type' => $post_type, 'posts_per_page' => 10, 'post_status' => 'any', 'meta_query' => array( array( 'key' => '_tribe_rsvp_for_event', 'value' => $post_id ) ) ) );
    $by_level = array();
    foreach ( $existing as $ticket ) {
        $level = get_post_meta( $ticket->ID, '_ufc_tier_level', true );
        if ( $level ) { $by_level[ $level ] = $ticket; }
    }

    $total       = 0;
    $event_start = get_post_meta( $post_id, '_EventStartDate', true );

    foreach ( array( 'or', 'argent', 'bronze' ) as $key ) {
        $cfg    = isset( $tiers[ $key ] ) ? $tiers[ $key ] : array();
        $active = ! empty( $cfg['active'] );
        $ticket = isset( $by_level[ $key ] ) ? $by_level[ $key ] : null;

        if ( $active ) {
            $name     = sanitize_text_field( $cfg['name'] ?? '' );
            $desc     = wp_kses_post( $cfg['desc'] ?? '' );
            $capacity = max( 1, absint( $cfg['capacity'] ?? 100 ) );

            $ticket_id = $ticket
                ? ( wp_update_post( array( 'ID' => $ticket->ID, 'post_title' => $name, 'post_content' => $desc, 'post_excerpt' => $desc, 'post_status' => 'publish' ) ) ? $ticket->ID : 0 )
                : wp_insert_post( array( 'post_title' => $name, 'post_content' => $desc, 'post_excerpt' => $desc, 'post_type' => $post_type, 'post_status' => 'publish', 'post_author' => get_current_user_id() ) );

            if ( $ticket_id && ! is_wp_error( $ticket_id ) ) {
                update_post_meta( $ticket_id, '_tribe_rsvp_for_event', $post_id );
                update_post_meta( $ticket_id, '_ufc_tier_level', $key );
                update_post_meta( $ticket_id, '_price', '0' );
                update_post_meta( $ticket_id, '_manage_stock', 'yes' );
                update_post_meta( $ticket_id, '_stock', (string) $capacity );
                update_post_meta( $ticket_id, '_tribe_ticket_show_description', 'yes' );
                update_post_meta( $ticket_id, '_tribe_ticket_show_not_going', 'yes' );
                update_post_meta( $ticket_id, '_tribe_ticket_capacity', (string) $capacity );
                update_post_meta( $ticket_id, '_capacity', (string) $capacity );
                update_post_meta( $ticket_id, '_ticket_start_date', current_time( 'Y-m-d H:i:s' ) );
                if ( $event_start ) { update_post_meta( $ticket_id, '_ticket_end_date', $event_start ); }
                $total += $capacity;
            }
        } elseif ( $ticket ) {
            wp_delete_post( $ticket->ID, true );
        }
    }

    if ( $total > 0 ) {
        update_post_meta( $post_id, '_tribe_default_ticket_provider', 'Tribe__Tickets__RSVP' );
        update_post_meta( $post_id, '_tribe_ticket_capacity', (string) $total );
        update_post_meta( $post_id, '_ticket_capacity', (string) $total );
    }
}
