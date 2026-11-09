<?php

if (!defined('ABSPATH')) {
    exit;
}

$email = was_site_contact_email();
$phone = was_site_business_settings()['telephone'];
$phone_label = preg_replace('/^\+55([0-9]{2})([0-9]{4,5})([0-9]{4})$/D', '+55 ($1) $2-$3', $phone);
$address = was_site_business_address();
?>
<address class="was-site-contact-details">
    <a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a>
    <a href="<?php echo esc_url('tel:' . $phone); ?>"><?php echo esc_html($phone_label); ?></a>
    <span>
        <?php if (!empty($address['streetAddress'])) : ?>
            <?php echo esc_html($address['streetAddress']); ?><br>
        <?php endif; ?>
        Curitiba, PR, Brasil<?php echo !empty($address['postalCode']) ? esc_html(' · CEP ' . $address['postalCode']) : ''; ?>
    </span>
</address>
