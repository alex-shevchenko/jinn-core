<?php


namespace Jinn\Generator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class PhpFileWriter
{
    public static function writeFile(string $filename, string $contents) {
        $folder = substr($filename, 0, strrpos($filename, '/'));
        self::ensureFolderExists($folder);

        file_put_contents($filename, $contents);
    }

    public static function writePhpFile(string $filename, PhpFile $file) {
        $printer = new PsrPrinter();

        self::writeFile($filename, $printer->printFile($file));
    }

    protected static function ensureFolderExists(string $folder): void {
        if (!is_dir($folder)) @mkdir($folder, 0755, true);
    }
}
