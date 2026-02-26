<?php

define('TRIEVE_API_URL', 'https://api.mintlifytrieve.com/api/chunk_group/group_oriented_autocomplete');
define('TRIEVE_DATASET_ID', 'd37f36e7-f12a-433a-8893-a5f2189647f5');
define('TRIEVE_API_KEY', 'tr-T6JLeTkFXeNbNPyhijtI9XhIncydQQ3O');
define('FILAMENT_DOCS_BASE_URL', 'https://filamentphp.com/docs/');

function getResults($query, $version)
{
    $versionTag = match ($version) {
        'v1' => 'VERSION:1.x',
        'v2' => 'VERSION:2.x',
        'v3' => 'VERSION:3.x',
        'v4' => 'VERSION:4.x',
        default => 'VERSION:5.x',
    };

    $payload = [
        'query' => $query,
        'search_type' => 'fulltext',
        'page_size' => 10,
        'group_size' => 1,
        'score_threshold' => 0.2,
        'filters' => [
            'must_not' => [['field' => 'tag_set', 'match' => ['code']]],
            'must' => [['field' => 'tag_set', 'match_any' => [$versionTag, 'VERSION:*']]]
        ]
    ];

    $ch = curl_init(TRIEVE_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'TR-Dataset: ' . TRIEVE_DATASET_ID,
        'Authorization: ' . TRIEVE_API_KEY,
        'X-API-Version: V2'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return [];
    }

    $data = json_decode($response, true);

    if (!isset($data['results']) || empty($data['results'])) {
        return [];
    }

    $results = [];
    $seen = [];

    foreach ($data['results'] as $group) {
        if (!isset($group['chunks'])) {
            continue;
        }

        foreach ($group['chunks'] as $chunkData) {
            $chunk = $chunkData['chunk'] ?? null;

            if (!$chunk) {
                continue;
            }

            $link = $chunk['link'] ?? null;
            $title = $chunk['metadata']['title'] ?? null;
            $breadcrumbs = $chunk['metadata']['breadcrumbs'] ?? [];

            if (!$link || !$title) {
                continue;
            }

            // Skip duplicates
            if (isset($seen[$link])) {
                continue;
            }
            $seen[$link] = true;

            $url = FILAMENT_DOCS_BASE_URL . $link;
            $subtitle = implode(' » ', $breadcrumbs);

            $results[] = [
                'id' => $chunk['id'],
                'title' => html_entity_decode($title),
                'subtitle' => html_entity_decode($subtitle),
                'url' => $url,
            ];
        }
    }

    return $results;
}
