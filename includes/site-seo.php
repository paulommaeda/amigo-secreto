<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', 'was_site_search_metadata', 5);
add_filter('document_title_parts', 'was_site_search_title');
add_filter('wp_robots', 'was_site_robots_meta');
add_action('template_redirect', 'was_site_private_headers', 0);
add_filter('robots_txt', 'was_site_robots_text', 20, 2);
add_action('do_robotstxt', 'was_site_robots_cache_control');
add_filter('wp_sitemaps_posts_query_args', 'was_site_sitemap_posts_args');
add_filter('posts_where', 'was_site_public_posts_where', 10, 2);
add_filter('wp_sitemaps_posts_entry', 'was_site_sitemap_post_date', 10, 2);
add_filter('wp_sitemaps_taxonomies_entry', 'was_site_sitemap_term_date', 10, 3);
add_filter('wp_sitemaps_users_entry', 'was_site_sitemap_author_date', 10, 2);
add_filter('wp_sitemaps_index_entry', 'was_site_sitemap_index_date', 10, 4);

function was_site_private_request() {
    $post = get_queried_object();
    if (!is_singular() || !($post instanceof WP_Post)) {
        return false;
    }

    $features = was_page_features();
    return !empty($features['interactive'])
        || get_post_meta($post->ID, '_was_is_landing_page', true) === 'yes'
        || (bool) get_post_meta($post->ID, '_was_linked_group_id', true)
        || was_site_current_role(['dashboard', 'login', 'register']) !== '';
}

function was_site_robots_meta($robots) {
    if (was_site_private_request() || (is_singular() && get_post_field('post_password', get_queried_object_id()) !== '')) {
        $robots['noindex'] = true;
    }
    return $robots;
}

function was_site_private_headers() {
    if (was_site_private_request()) {
        header('X-Robots-Tag: noindex');
        nocache_headers();
        do_action('litespeed_control_set_nocache', 'Amigo Secreto account or invitation');
    }
}

function was_site_robots_text($output, $public) {
    if (!$public || !was_site_public_enabled() || preg_match('/^\h*Content-Signal\h*:/mi', $output)) {
        return $output;
    }
    $line = "Content-Signal: search=yes, ai-input=yes, ai-train=no\n";
    if (preg_match('/^\h*User-agent:\h*\*\h*(?:#[^\r\n]*)?\r?\n/mi', $output)) {
        return preg_replace('/^\h*User-agent:\h*\*\h*(?:#[^\r\n]*)?\r?\n/mi', '$0' . $line, $output, 1);
    }
    return rtrim($output) . "\n\nUser-agent: *\n" . $line;
}

function was_site_robots_cache_control() {
    if (was_site_public_enabled()) {
        header('Cache-Control: public, max-age=300, must-revalidate');
        do_action('litespeed_control_set_ttl', 300, 'Amigo Secreto robots.txt');
    }
}

function was_site_sitemap_posts_args($args) {
    $private_pages = array_filter(array_map('was_site_page_id', ['dashboard', 'login', 'register']));
    $args['post__not_in'] = array_values(array_unique(array_merge($args['post__not_in'] ?? [], $private_pages)));
    $args['has_password'] = false;
    $args['was_site_public_only'] = true;
    return $args;
}

function was_site_public_posts_where($where, $query) {
    if (!$query->get('was_site_public_only')) {
        return $where;
    }

    global $wpdb;
    // Match the draw and dashboard shortcodes, including legacy pages without plugin metadata.
    $shortcode = '\[amigo_secreto(_dashboard)?([[:space:]/]|\])';
    $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_content NOT REGEXP %s", $shortcode);
    return $where . " AND NOT EXISTS (
        SELECT 1 FROM {$wpdb->postmeta} AS was_private
        WHERE was_private.post_id = {$wpdb->posts}.ID
        AND (was_private.meta_key = '_was_linked_group_id'
            OR (was_private.meta_key = '_was_is_landing_page' AND was_private.meta_value = 'yes'))
    )";
}

function was_site_sitemap_post_date($entry, $post) {
    static $site_indexes = null;
    if ($site_indexes === null) {
        $site_indexes = [was_site_page_id('home'), was_site_page_id('blog')];
    }
    $date = get_post_modified_time(DATE_W3C, true, $post) ?: get_post_time(DATE_W3C, true, $post);
    if (in_array($post->ID, $site_indexes, true)) {
        static $last_article = null;
        if ($last_article === null) {
            $last_article = was_site_public_articles(1, '', 'modified')[0] ?? false;
        }
        if ($last_article) {
            $date = max($date, get_post_modified_time(DATE_W3C, true, $last_article));
        }
    }
    if ($date) {
        $entry['lastmod'] = $date;
    }
    return $entry;
}

function was_site_sitemap_archive_dates($authors) {
    static $dates = [];
    $key = $authors ? 'author' : 'term';
    if (!isset($dates[$key])) {
        global $wpdb;
        $source = $authors
            ? "SELECT p.post_author AS archive_id, MAX(p.post_modified_gmt) AS modified FROM {$wpdb->posts} AS p"
            : "SELECT r.term_taxonomy_id AS archive_id, MAX(p.post_modified_gmt) AS modified
               FROM {$wpdb->posts} AS p INNER JOIN {$wpdb->term_relationships} AS r ON r.object_id = p.ID";
        $rows = $wpdb->get_results($source . " WHERE p.post_status = 'publish' AND p.post_type <> 'attachment' GROUP BY archive_id");
        $dates[$key] = [];
        foreach ($rows as $row) {
            if ($row->modified && $row->modified !== '0000-00-00 00:00:00') {
                $dates[$key][$row->archive_id] = gmdate(DATE_W3C, strtotime($row->modified . ' UTC'));
            }
        }
    }
    return $dates[$key];
}

function was_site_sitemap_term_date($entry, $id, $taxonomy) {
    $term = get_term($id, $taxonomy);
    if (!($term instanceof WP_Term)) {
        return $entry;
    }
    $dates = was_site_sitemap_archive_dates(false);
    $date = $dates[$term->term_taxonomy_id] ?? '';
    if (is_taxonomy_hierarchical($taxonomy)) {
        foreach ((array) get_term_children($id, $taxonomy) as $child_id) {
            $child = get_term($child_id, $taxonomy);
            if ($child instanceof WP_Term) {
                $date = max($date, $dates[$child->term_taxonomy_id] ?? '');
            }
        }
    }
    if ($date) {
        $entry['lastmod'] = $date;
    }
    return $entry;
}

function was_site_sitemap_author_date($entry, $user) {
    $date = was_site_sitemap_archive_dates(true)[$user->ID] ?? '';
    if ($date) {
        $entry['lastmod'] = $date;
    }
    return $entry;
}

function was_site_sitemap_index_date($entry, $object_type, $subtype, $page) {
    $provider_name = ['post' => 'posts', 'term' => 'taxonomies', 'user' => 'users'][$object_type] ?? '';
    $provider = wp_get_sitemap_providers()[$provider_name] ?? null;
    if ($provider) {
        $dates = array_column($provider->get_url_list($page, $subtype), 'lastmod');
        if ($dates) {
            $entry['lastmod'] = max($dates);
        }
    }
    return $entry;
}

function was_site_owns_search_metadata() {
    return apply_filters('was_site_owns_search_metadata', !(
        defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')
        || defined('SEOPRESS_VERSION') || defined('THE_SEO_FRAMEWORK_VERSION')
    ));
}

function was_site_search_title($parts) {
    if (!was_site_owns_search_metadata()) {
        return $parts;
    }
    if (was_site_view() === 'home') {
        $parts['title'] = 'Sorteio de Amigo Secreto Online Grátis';
        $parts['site'] = was_site_brand();
        unset($parts['tagline']);
    } elseif (was_site_view() === 'blog' && is_home()) {
        $parts['title'] = 'Blog de Amigo Secreto';
    }
    return $parts;
}

function was_site_archive_url() {
    if (is_home()) {
        $url = was_site_page_url('blog');
    } elseif (is_category() || is_tag()) {
        $url = get_term_link(get_queried_object());
    } elseif (is_author()) {
        $url = get_author_posts_url(get_queried_object_id());
    } elseif (is_day()) {
        $url = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
    } elseif (is_month()) {
        $url = get_month_link(get_query_var('year'), get_query_var('monthnum'));
    } else {
        $url = get_year_link(get_query_var('year'));
    }
    if (is_wp_error($url)) {
        return '';
    }
    $page = (int) get_query_var('paged');
    if ($page > 1) {
        global $wp_rewrite;
        $url = $wp_rewrite->using_permalinks() && !wp_parse_url($url, PHP_URL_QUERY)
            ? user_trailingslashit(trailingslashit($url) . $wp_rewrite->pagination_base . '/' . $page, 'paged')
            : add_query_arg('paged', $page, $url);
    }
    return $url;
}

function was_site_search_metadata() {
    $view = was_site_view();
    $tutorial = is_singular() && has_shortcode(get_post_field('post_content', get_queried_object_id()), 'amigo_secreto_tutorial');
    if (!was_site_owns_search_metadata() || !get_option('blog_public') || is_search() || is_preview()
        || (!in_array($view, ['home', 'blog', 'article', 'info'], true) && !$tutorial)
        || was_site_private_request()) {
        return;
    }

    $post = is_singular() ? get_queried_object() : null;
    if ($post && ($post->post_password !== '' || $post->post_status !== 'publish')) {
        return;
    }
    $url = $post ? wp_get_canonical_url($post) : was_site_archive_url();
    if (!$url) {
        return;
    }

    $description = was_site_description();
    $role = was_site_current_role(['about', 'contact', 'terms', 'privacy']);
    if ($view === 'article') {
        $description = was_site_excerpt($post) ?: $description;
    } elseif ($view === 'blog') {
        $description = 'Guias de organização, regras do sorteio e ideias de presentes para a próxima confraternização.';
        if (is_archive()) {
            $description = was_site_plain_text(get_the_archive_description())
                ?: sprintf('Artigos sobre %s no blog do %s.', was_site_plain_text(get_the_archive_title()), was_site_brand());
        }
    } elseif ($view === 'info') {
        $description = was_site_excerpt($post) ?: $description;
    } elseif ($tutorial) {
        $description = 'Aprenda a criar um grupo de amigo secreto, convidar pelo WhatsApp e consultar o resultado usando o PIN.';
    }

    $title = was_site_plain_text(wp_get_document_title());
    $thumbnail = $view === 'article' ? get_post_thumbnail_id($post) : 0;
    [$image, $width, $height] = ($thumbnail ? wp_get_attachment_image_src($thumbnail, 'large') : false)
        ?: [plugins_url('assets/site-share.jpg', WAS_PLUGIN_FILE), 1200, 630];
    $image_alt = $thumbnail ? get_post_meta($thumbnail, '_wp_attachment_image_alt', true) : '';
    $type = $view === 'article' ? 'article' : 'website';
    $tags = [
        'description' => $description,
        'og:title' => $title,
        'og:description' => $description,
        'og:url' => $url,
        'og:type' => $type,
        'og:site_name' => was_site_brand(),
        'og:locale' => str_replace('-', '_', get_bloginfo('language')),
        'og:image' => $image,
        'og:image:width' => $width,
        'og:image:height' => $height,
        'og:image:alt' => $image_alt ?: ($thumbnail ? get_the_title($post) : 'Seu Amigo Secreto. Sorteie online com um link.'),
        'twitter:card' => 'summary_large_image',
    ];
    if ($view === 'article') {
        $tags['article:published_time'] = get_post_time(DATE_W3C, true, $post);
        $tags['article:modified_time'] = get_post_modified_time(DATE_W3C, true, $post);
    }
    foreach (array_filter($tags) as $key => $value) {
        $attribute = strpos($key, ':') !== false && strpos($key, 'twitter:') !== 0 ? 'property' : 'name';
        printf("<meta %s=\"%s\" content=\"%s\">\n", $attribute, esc_attr($key), esc_attr($value));
    }
    if (!$post) {
        printf("<link rel=\"canonical\" href=\"%s\">\n", esc_url($url));
    }

    $schema = was_site_schema($view, $role, $url, $description, $image, $post);
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP) . "</script>\n";
}

function was_site_schema($view, $role, $url, $description, $image, $post) {
    $home = home_url('/');
    $language = get_bloginfo('language');
    $name = was_site_brand();
    $email = was_site_contact_email();
    $phone = was_site_business_settings()['telephone'];
    $publisher = ['@id' => $home . '#organization'];
    $website = ['@id' => $home . '#website'];
    $webpage = ['@id' => $url . '#webpage'];
    $founder = ['@id' => $home . '#paulo-maeda'];
    $application = ['@id' => $home . '#application'];
    $service = ['@id' => $home . '#service'];
    $types = ['about' => 'AboutPage', 'contact' => 'ContactPage'];
    $page_type = ['home' => 'FAQPage', 'blog' => 'CollectionPage'][$view] ?? ($types[$role] ?? 'WebPage');

    $graph = [
        [
            '@type' => 'Organization',
            '@id' => $publisher['@id'],
            'name' => $name,
            'url' => $home,
            'description' => was_site_description(),
            'logo' => get_site_icon_url(512) ?: plugins_url('assets/site-mark.svg', WAS_PLUGIN_FILE),
            'email' => $email,
            'telephone' => $phone,
            'address' => was_site_business_address(),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Suporte',
                'email' => $email,
                'telephone' => $phone,
                'availableLanguage' => 'Português',
                'url' => was_site_page_url('contact') ?: $home . '#contato',
            ],
            'sameAs' => ['https://github.com/paulommaeda/amigo-secreto'],
            'founder' => $founder,
        ],
        [
            '@type' => 'Person',
            '@id' => $founder['@id'],
            'name' => 'Paulo Maeda',
            'url' => was_site_page_url('about') ?: 'https://paulomaeda.com/',
            'sameAs' => ['https://www.linkedin.com/in/paulomaeda/', 'https://paulomaeda.com/'],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $website['@id'],
            'url' => $home,
            'name' => $name,
            'publisher' => $publisher,
            'inLanguage' => $language,
        ],
    ];

    $page = [
        '@type' => $page_type,
        '@id' => $webpage['@id'],
        'url' => $url,
        'name' => $view === 'home' ? 'Sorteio de Amigo Secreto Online Grátis' : was_site_plain_text(wp_get_document_title()),
        'description' => $description,
        'isPartOf' => $website,
        'inLanguage' => $language,
    ];
    if ($view === 'home') {
        $page['about'] = [$application, $service];
        $page['mainEntity'] = array_map(static function ($pair) {
            return [
                '@type' => 'Question',
                'name' => $pair[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair[1]],
            ];
        }, was_site_questions());
        $graph[] = [
            '@type' => 'WebApplication',
            '@id' => $application['@id'],
            'name' => $name,
            'url' => $home,
            'description' => was_site_description(),
            'applicationCategory' => 'UtilitiesApplication',
            'operatingSystem' => 'Web',
            'publisher' => $publisher,
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'BRL'],
        ];
        $graph[] = [
            '@type' => 'Service',
            '@id' => $service['@id'],
            'name' => 'Sorteio de amigo secreto online',
            'serviceType' => 'Organização de sorteios de amigo secreto',
            'url' => $home,
            'description' => was_site_description(),
            'provider' => $publisher,
        ];
    } elseif ($view === 'article') {
        $author = get_userdata($post->post_author);
        $article = [
            '@type' => 'BlogPosting',
            '@id' => $url . '#article',
            'mainEntityOfPage' => $webpage,
            'headline' => was_site_plain_text(get_the_title($post)),
            'description' => $description,
            'image' => $image,
            'datePublished' => get_post_time(DATE_W3C, true, $post),
            'dateModified' => get_post_modified_time(DATE_W3C, true, $post),
            'publisher' => $publisher,
            'inLanguage' => $language,
        ];
        if ($author) {
            $article['author'] = ['@type' => 'Person', 'name' => $author->display_name, 'url' => get_author_posts_url($author->ID)];
        }
        $page['mainEntity'] = ['@id' => $article['@id']];
        $graph[] = $article;
    } elseif ($role === 'about' || $role === 'contact') {
        $page['about'] = $role === 'about' ? [$publisher, $founder] : $publisher;
    }
    $graph[] = $page;

    return ['@context' => 'https://schema.org', '@graph' => $graph];
}
