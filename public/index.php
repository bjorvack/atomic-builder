<?php

use Bjorvack\AtomicBuilder\Twig\TwigMacroLoader;

require_once '../vendor/autoload.php';

$directories = [];

foreach (scandir ("../src/Components") as $directory) {
    if ($directory != '.' && $directory != '..') {
        foreach (scandir ("../src/Components/" . $directory) as $subdirectory) {
            if ($subdirectory != '.' && $subdirectory != '..') {
                $directories[] = $directory . '/' . $subdirectory;
            }
        }
    }
}

$loader = new Twig_Loader_Filesystem(
    $directories,
    "../src/Components"
);

$twig = new Twig_Environment($loader, ['debug' => true]);
$twig->addExtension(new Twig_Extension_Debug());

$twig->addExtension(
    new TwigMacroLoader("../src/Components")
);

echo $twig->render('index.php.twig', []);