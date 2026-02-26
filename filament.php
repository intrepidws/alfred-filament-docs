<?php

error_reporting(0);

use Alfred\Workflows\Workflow;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

$icons = ['icon-sparky.png', 'icon-atlas.png', 'icon-barney.png'];

$query = $argv[1];
$version = isset($argv[2]) ? $argv[2] : 'v5';

$workflow = new Workflow;

$results = getResults($query, $version);

if (empty($results)) {
    $workflow->result()
        ->title('No matches')
        ->icon('google.png')
        ->subtitle("No match found in the docs. Search Google for: \"Laravel+Filament+{$query}\"")
        ->arg('https://www.google.com/search?q=laravel+filament+' . $query)
        ->quicklookurl('https://www.google.com/search?q=laravel+filament+' . $query)
        ->valid(true);

    echo $workflow->output();

    exit;
}

foreach ($results as $hit) {
    $workflow->result()
        ->uid($hit['id'])
        ->title($hit['title'])
        ->autocomplete($hit['title'])
        ->subtitle($hit['subtitle'])
        ->arg($hit['url'])
        ->quicklookurl($hit['url'])
        ->icon($icons[array_rand($icons)])
        ->valid(true);
}

echo $workflow->output();
