<?php

namespace Tiknil\SvgSprite;


class BundleSprite
{
    public function __construct(
        private string $folder,
        private string $outFile,
        private string $prefix = ""
    )
    {
    }

    public function execute(): int
    {

        $this->print("Searching svg files in {$this->folder}");

        $svgFiles = $this->svgFiles();

        $tot = count($svgFiles);
        $this->print("Found $tot svg files");


        $sprite = $this->createBundleDocument($svgFiles);
        $sprite->saveHTMLFile($this->outFile);

        file_put_contents(
            $this->outFile, 
            str_replace('xmlns:default="http://www.w3.org/2000/svg" ', '', file_get_contents($this->outFile))
        );

        $this->print("Created sprite at {$this->outFile}");

        return 0;
    }

    private function print(string $msg)
    {
        echo "$msg\n";
    }

    private function idForFile(string $file): string
    {
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $baseName);
        return $this->prefix . $sanitizedName;
    }

    private function svgFiles(): array
    {
        $svgFiles = [];

        // Get all files in the directory
        $files = scandir($this->folder);

        // Loop through each file
        foreach ($files as $fileName) {
            // Skip . and .. directory references
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $filePath = $this->folder . DIRECTORY_SEPARATOR . $fileName;

            // If it's a file (not a subdirectory)
            if (is_file($filePath)) {
                // Check if it's an SVG file by extension
                if (pathinfo($filePath, PATHINFO_EXTENSION) === 'svg') {
                    $svgFiles[] = $fileName;
                }
            }
        }

        return $svgFiles;
    }

    private function createBundleDocument(array $svgFiles): \DOMDocument
    {
        $spriteDoc = new \DOMDocument();
        $spriteDoc->preserveWhiteSpace = false;
        $spriteDoc->formatOutput = true;

        // Create the root SVG element for the sprite
        $rootSvg = $spriteDoc->createElementNS('http://www.w3.org/2000/svg', 'svg');
        $rootSvg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $rootSvg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $rootSvg->setAttribute('style', 'display: none;');
        $spriteDoc->appendChild($rootSvg);

        foreach ($svgFiles as $file) {
            $symbol = $this->convertFile($this->folder . DIRECTORY_SEPARATOR . $file, $this->idForFile($file));

            if (empty($symbol)) continue;

            $importedSymbol = $spriteDoc->importNode($symbol, true);
            $rootSvg->appendChild($spriteDoc->createTextNode("\n\n"));
            $rootSvg->appendChild($importedSymbol);
        }

        return $spriteDoc;

    }

    private function convertFile(string $filePath, string $symbolId): ?\DOMElement
    {
        $svgContent = file_get_contents($filePath);

        // Create a new DOMDocument
        $doc = new \DOMDocument();

        // Preserve whitespace for proper formatting
        $doc->preserveWhiteSpace = true;
        $doc->formatOutput = true;

        // Load the SVG content
        $doc->loadXML($svgContent);

        // Get the root SVG element
        $svgElement = $doc->documentElement;

        if (!$svgElement || $svgElement->nodeName !== 'svg') {
            $this->print("$filePath doesn't contain a valid SVG root element, skipping it");
            return null;
        }

        // Extract important attributes from the SVG tag
        $viewBox = $svgElement->getAttribute('viewBox');
        $width = $svgElement->getAttribute('width');
        $height = $svgElement->getAttribute('height');

        // Create a new symbol element
        $symbolElement = $doc->createElement('symbol');
        $symbolElement->setAttribute('id', $symbolId);

        if ($viewBox) {
            $symbolElement->setAttribute('viewBox', $viewBox);
        } else if ($width && $height) {
            // Create viewBox from width and height if viewBox isn't present
            $symbolElement->setAttribute('viewBox', "0 0 $width $height");
        }

        foreach ($svgElement->attributes as $attr) {
             if (in_array($attr->name, ['width', 'height', 'viewBox', 'class', 'id'])) {
                continue; // Skip width, height, and viewBox attributes
             }

             $symbolElement->setAttribute($attr->name, $attr->value);
        }

        // Copy all child nodes from SVG to symbol
        while ($svgElement->childNodes->length > 0) {
            $symbolElement->appendChild($svgElement->childNodes->item(0));
        }

        return $symbolElement;
    }


}
