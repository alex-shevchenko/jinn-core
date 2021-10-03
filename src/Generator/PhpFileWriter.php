<?php


namespace Jinn\Generator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class PhpFileWriter
{
    public static function writePhpFile(string $filename, PhpFile $file) {
        $folder = substr($filename, 0, strrpos($filename, '/'));
        self::ensureFolderExists($folder);

        $printer = new PsrPrinter();

        file_put_contents($filename, $printer->printFile($file));
    }

    protected static function ensureFolderExists(string $folder): void {
        if (!is_dir($folder)) @mkdir($folder, 07555, true);
    }
}
