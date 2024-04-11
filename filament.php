<?php

use Alfred\Workflows\Workflow;

use Algolia\AlgoliaSearch\SearchClient as Algolia;
use Algolia\AlgoliaSearch\Support\UserAgent as AlgoliaUserAgent;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

$query = $argv[1];
$version = isset($argv[2]) ? $argv[2] : 'v3';

$workflow = new Workflow;
$algolia = Algolia::create('LMIKXMDI4P', '1e3d12b0b9c3a4db16cd896e83b9efa0');

AlgoliaUserAgent::addCustomUserAgent('Filament Alfred Workflow', '3.0.0');

$results = getResults($algolia, 'filamentadmin', $query, $version);

if (empty($results)) {
    $workflow->result()
        ->title('No matches')
        ->icon('google.png')
        ->subtitle('No match found in the docs. Search Google for: "Laravel+Filament+Admin+{$query}"')
        ->arg('https://www.google.com/search?q=laravel+filament+admin+' . $query)
        ->quicklookurl('https://www.google.com/search?q=laravel+filament+admin+' . $query)
        ->valid(true);

    echo $workflow->output();

    exit;
}

foreach ($results as $hit) {
    list($title, $titleLevel) = getTitle($hit);

    if ($title === null) {
        continue;
    }

    $title = html_entity_decode($title);

    $workflow->result()
        ->uid($hit['objectID'])
        ->title($title)
        ->autocomplete($title)
        ->subtitle(html_entity_decode(getSubtitle($hit, $titleLevel)))
        ->arg($hit['url'])
        ->quicklookurl($hit['url'])
        ->valid(true);
}

echo $workflow->output();
