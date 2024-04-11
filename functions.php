<?php

function getResults($algolia, $indexName, $query, $version)
{
    if ($version === 'v1') {
        $facetFilter = ['version:1.x'];
    } elseif ($version === 'v2') {
        $facetFilter = ['version:2.x'];
    } else {
        $facetFilter = ['version:3.x'];
    }

    $params = ['facetFilters' => $facetFilter];

    $index = $algolia->initIndex($indexName);

    return $index->search($query, $params)['hits'];
}

function getTitle($hit)
{
    if (isset($hit['hierarchy']['lvl6'])) {
        return [$hit['hierarchy']['lvl6'], 6];
    }

    if (isset($hit['hierarchy']['lvl5'])) {
        return [$hit['hierarchy']['lvl5'], 5];
    }

    if (isset($hit['hierarchy']['lvl4'])) {
        return [$hit['hierarchy']['lvl4'], 4];
    }

    if (isset($hit['hierarchy']['lvl3'])) {
        return [$hit['hierarchy']['lvl3'], 3];
    }

    if (isset($hit['hierarchy']['lvl2'])) {
        return [$hit['hierarchy']['lvl2'], 2];
    }

    if (isset($hit['hierarchy']['lvl1'])) {
        return [$hit['hierarchy']['lvl1'], 1];
    }

    return [null, null];
}

function getSubtitle($hit, $titleLevel)
{
    $currentLevel = 0;
    $subtitle = $hit['hierarchy']['lvl0'];

    while ($currentLevel < $titleLevel) {
        $currentLevel++;
        $subtitle .= ' » ' . $hit['hierarchy']['lvl' . $currentLevel];
    }

    return $subtitle;
}
