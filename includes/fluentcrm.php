<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_sync_fluentcrm($group, array $person) {
    try {
        $name = explode(' ', $person['name'], 2);
        $contact = FluentCrmApi('contacts')->createOrUpdate([
            'first_name' => $name[0],
            'last_name' => $name[1] ?? '',
            'email' => $person['email'],
            'status' => 'subscribed',
            'phone' => $person['whatsapp'],
        ]);
        if (is_wp_error($contact) || !$contact) {
            error_log('[WP Amigo Secreto] Não foi possível sincronizar o contato no FluentCRM.');
            return;
        }

        if (class_exists('\FluentCrm\App\Models\Tag')) {
            $general_tag = \FluentCrm\App\Models\Tag::firstOrCreate(['title' => 'Amigo Secreto', 'slug' => 'amigo-secreto']);
            $group_tag = \FluentCrm\App\Models\Tag::firstOrCreate([
                'title' => 'Sorteio: ' . $group->title,
                'slug' => sanitize_title('sorteio-' . $group->title),
            ]);
            $contact->attachTags([$general_tag->id, $group_tag->id]);
        }

        if (class_exists('\FluentCrm\App\Models\SubscriberNote')) {
            \FluentCrm\App\Models\SubscriberNote::create([
                'subscriber_id' => $contact->id,
                'created_by' => get_current_user_id() ?: 1,
                'title' => 'Inscrição Amigo Secreto',
                'description' => 'Entrou no sorteio <strong>' . esc_html($group->title)
                    . '</strong><br>PIN de acesso <strong>' . esc_html($person['token']) . '</strong>',
                'type' => 'normal',
            ]);
        }
    } catch (Throwable $error) {
        error_log('[WP Amigo Secreto] Erro na integração com o FluentCRM.');
    }
}
