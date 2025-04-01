<?php

namespace Tiknil\SvgSprite;

$idPrefix = "";

// Search prefix
for ($i = 1; $i < $argc - 1; $i++) {
    if ($argv[$i] === '--prefix' || $argv[$i] === '-p') {
        $idPrefix = $argv[$i+1];

        array_splice($argv, $i, 2);
        break;
    }
}

$svgDirectory = $argv[1];
$outputSprite = $argv[2];

$cmd = new BundleSprite($svgDirectory, $outputSprite, $idPrefix);

return $cmd->execute();
