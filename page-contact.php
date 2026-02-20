<?php
/* J'ai codé ici — Template page Contact */
get_header();
?>

<section class="ufc-contact-section">
    <div class="ufc-contact-grid">

        <div class="ufc-contact-info ufc-animate">
            <h2 class="ufc-contact-info-title"><?php esc_html_e( 'Nos coordonnées', 'astra-child' ); ?></h2>

            <div class="ufc-contact-info-item">
                <div class="ufc-contact-info-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <h3><?php esc_html_e( 'Email', 'astra-child' ); ?></h3>
                    <p><?php esc_html_e( 'contact@ufc-community.fr', 'astra-child' ); ?></p>
                </div>
            </div>

            <div class="ufc-contact-info-item">
                <div class="ufc-contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <h3><?php esc_html_e( 'Adresse', 'astra-child' ); ?></h3>
                    <p><?php esc_html_e( 'Paris, France', 'astra-child' ); ?></p>
                </div>
            </div>

            <div class="ufc-contact-info-item">
                <div class="ufc-contact-info-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3><?php esc_html_e( 'Horaires', 'astra-child' ); ?></h3>
                    <p><?php esc_html_e( 'Lun - Ven : 9h - 18h', 'astra-child' ); ?></p>
                </div>
            </div>

            <div class="ufc-contact-info-item">
                <div class="ufc-contact-info-icon"><i class="fas fa-phone"></i></div>
                <div>
                    <h3><?php esc_html_e( 'Téléphone', 'astra-child' ); ?></h3>
                    <p><?php esc_html_e( '+33 1 23 45 67 89', 'astra-child' ); ?></p>
                </div>
            </div>

            <div class="ufc-contact-socials">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <?php
        // =====================================================================
        // C'EST ICI — Formulaire de contact (non fonctionnel)
        // Le formulaire est purement visuel (HTML/CSS). Il ne traite aucune
        // donnee et n'envoie rien. Le bouton "Envoyer" ne declenche aucune action.
        // Pour le rendre fonctionnel, il faudrait ajouter un plugin (ex: WPForms,
        // Contact Form 7) ou un traitement PHP custom avec wp_mail().
        // =====================================================================
        ?>
        <div class="ufc-contact-form-wrapper ufc-animate">
            <h2 class="ufc-contact-form-title"><?php esc_html_e( 'Envoyez-nous un message', 'astra-child' ); ?></h2>
            <form class="ufc-contact-form" onsubmit="return false;">
                <div class="ufc-contact-form-row">
                    <div class="ufc-contact-form-field">
                        <label for="ufc-contact-name"><?php esc_html_e( 'Nom', 'astra-child' ); ?></label>
                        <input type="text" id="ufc-contact-name" placeholder="<?php esc_attr_e( 'Votre nom', 'astra-child' ); ?>">
                    </div>
                    <div class="ufc-contact-form-field">
                        <label for="ufc-contact-email"><?php esc_html_e( 'Email', 'astra-child' ); ?></label>
                        <input type="email" id="ufc-contact-email" placeholder="<?php esc_attr_e( 'Votre email', 'astra-child' ); ?>">
                    </div>
                </div>
                <div class="ufc-contact-form-field">
                    <label for="ufc-contact-subject"><?php esc_html_e( 'Sujet', 'astra-child' ); ?></label>
                    <input type="text" id="ufc-contact-subject" placeholder="<?php esc_attr_e( 'Sujet de votre message', 'astra-child' ); ?>">
                </div>
                <div class="ufc-contact-form-field">
                    <label for="ufc-contact-message"><?php esc_html_e( 'Message', 'astra-child' ); ?></label>
                    <textarea id="ufc-contact-message" rows="6" placeholder="<?php esc_attr_e( 'Votre message...', 'astra-child' ); ?>"></textarea>
                </div>
                <button type="submit" class="ufc-contact-submit">
                    <i class="fas fa-paper-plane"></i> <?php esc_html_e( 'Envoyer', 'astra-child' ); ?>
                </button>
            </form>
        </div>

    </div>
</section>

<?php get_footer(); ?>
