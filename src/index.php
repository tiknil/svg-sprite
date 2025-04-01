<?php

namespace Tiknil\SvgSprite;

use Symfony\Component\Console\Application;

$version = \Composer\InstalledVersions::getPrettyVersion('tiknil/svg-sprite');

$app = new Application("Tiknil svg sprite", $version ?? 'dev');

$cmd = new BundleSprite();
$app->add($cmd);
$app->setDefaultCommand($cmd->getName(), true);
$app->run();
