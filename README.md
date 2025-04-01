[Svg sprite](https://github.com/tiknil/svg-sprite) is a tool for creating an svg sprite from a folder of icons.

[Learn more about SVG Sprites and how to use them](https://css-tricks.com/svg-sprites-use-better-icon-fonts/)


## Installation

### Global installation
```
composer global require tiknil/svg-sprite
```

Make sure that the composer binaries directory is in your `$PATH`

You can update Skipper to the latest version by running

```
composer global update tiknil/svg-sprite
```

You can now use the `svg-sprite` command anywhere

### PHP project local installation

```
composer require tiknil/svg-sprite
```

You can now invoke the command using `vendor/bin/svg-sprite`


### Usage


```bash
svg-sprite <folder> <output>
```
For example, in a laravel project:
```
svg-sprite public/icons resources/views/sprite.blade.php
```

By default, the files names will be used as ID for the icon. You can add a prefix with the -p option:

```
svg-sprite  public/icons resources/views/sprite.blade.php -p icons-
```
