<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<form class="was-site-search" method="get" action="<?php echo esc_url(home_url('/')); ?>" role="search" aria-label="Buscar no blog">
    <label class="was-site-visually-hidden" for="was-site-search-input">Buscar no blog</label>
    <input id="was-site-search-input" type="search" name="s" value="<?php echo esc_attr(get_search_query(false)); ?>" placeholder="Buscar artigos…" required>
    <input type="hidden" name="post_type" value="post">
    <button type="submit" aria-label="Buscar"><?php was_site_icon('search'); ?></button>
</form>
