<?php

namespace Tiknil\SvgSprite;


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('svg-sprite')]
class BundleSprite extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('folder', InputArgument::REQUIRED, 'The directory with the svg files')
            ->addArgument('output', InputArgument::REQUIRED, 'The output file')
            ->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'Prefix added to fileName for each file');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = $input->getArgument('folder');
        $outFile = $input->getArgument('output');
        $prefix = $input->getOption('prefix', '');


        $output->writeLn("Searching svg files in $folder");

        $svgFiles = $this->svgFiles($folder);

        $tot = count($svgFiles);
        $output->writeLn("Found $tot svg files");


        $sprite = $this->createBundleDocument($folder, $svgFiles, $prefix);
        $htmlString = $sprite->saveHTML();
        file_put_contents($outFile, $htmlString);

        $output->writeLn("Created sprite at $outFile");


        return self::SUCCESS;
    }

    private function idForFile(string $file, string $prefix = ""): string
    {
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $baseName);
        return $prefix . $sanitizedName;
    }

    private function svgFiles(string $folder): array
    {
        $svgFiles = [];

        // Get all files in the directory
        $files = scandir($folder);

        // Loop through each file
        foreach ($files as $fileName) {
            // Skip . and .. directory references
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $filePath = $folder . DIRECTORY_SEPARATOR . $fileName;

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

    private function createBundleDocument(string $rootFolder, array $svgFiles, string $prefix): \DOMDocument
    {
        $spriteDoc = new \DOMDocument('1.0', 'UTF-8');
        $spriteDoc->preserveWhiteSpace = true;
        $spriteDoc->formatOutput = true;

        // Create the root SVG element for the sprite
        $rootSvg = $spriteDoc->createElementNS('http://www.w3.org/2000/svg', 'svg');
        $rootSvg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $rootSvg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $rootSvg->setAttribute('style', 'display: none;');
        $spriteDoc->appendChild($rootSvg);

        foreach ($svgFiles as $file) {
            $symbol = $this->convertFile($rootFolder . DIRECTORY_SEPARATOR . $file, $this->idForFile($file, $prefix));

            if (empty($symbol)) continue;

            $importedSymbol = $spriteDoc->importNode($symbol, true);
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
        $doc->loadXML($svgContent, LIBXML_NOERROR);

        // Get the root SVG element
        $svgElement = $doc->documentElement;

        if (!$svgElement || $svgElement->nodeName !== 'svg') {
            $this->error("$filePath doesn't contain a valid SVG root element, skipping it");
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

        // Copy all child nodes from SVG to symbol
        while ($svgElement->childNodes->length > 0) {
            $symbolElement->appendChild($svgElement->childNodes->item(0));
        }

        return $symbolElement;
    }


}
