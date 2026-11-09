<?php

if (!defined('ABSPATH')) {
    exit;
}

function was_sync_fluentcrm($group, array $person, $marketing_opt_in) {
    try {
        $api = FluentCrmApi('contacts');
        $existing = $api->getContact($person['email']);
        $name = explode(' ', $person['name'], 2);
        $data = [
            'first_name' => $name[0],
            'last_name' => $name[1] ?? '',
            'email' => $person['email'],
            'phone' => $person['whatsapp'],
        ];
        if (!$existing) {
            $data['status'] = $marketing_opt_in ? 'subscribed' : 'transactional';
        } elseif ($marketing_opt_in && $existing->status === 'transactional') {
            $data['status'] = 'subscribed';
        }
        $contact = $api->createOrUpdate($data);
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
            $tags = [$general_tag->id, $group_tag->id];
            if ($marketing_opt_in && $contact->status === 'subscribed') {
                $news_tag = \FluentCrm\App\Models\Tag::firstOrCreate([
                    'title' => 'Amigo Secreto - Novidades',
                    'slug' => 'amigo-secreto-novidades',
                ]);
                $tags[] = $news_tag->id;
            }
            $contact->attachTags($tags);
        }

        if (class_exists('\FluentCrm\App\Models\SubscriberNote')) {
            \FluentCrm\App\Models\SubscriberNote::create([
                'subscriber_id' => $contact->id,
                'created_by' => get_current_user_id(),
                'title' => 'Inscrição Amigo Secreto',
                'description' => 'Entrou no sorteio <strong>' . esc_html($group->title) . '</strong>.'
                    . ($marketing_opt_in ? '<br>Solicitou receber dicas e novidades por e-mail.' : ''),
                'type' => 'normal',
            ]);
        }
    } catch (Throwable $error) {
        error_log('[WP Amigo Secreto] Erro na integração com o FluentCRM.');
    }
}
