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

$svgDirectory = $argv[1] ?? null;
$outputSprite = $argv[2] ?? null;

if ($svgDirectory === null || $outputSprite === null) {
    echo "Missing required arguments.\n\nUsage: php index.php [--prefix <id-prefix>] <svg-directory> <output-file>\n";
    exit(1);
}

$cmd = new BundleSprite($svgDirectory, $outputSprite, $idPrefix);

return $cmd->execute();
