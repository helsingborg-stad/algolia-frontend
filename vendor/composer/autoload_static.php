<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0f834ea25fa694cc8a9a9ea02e9ac932
{
    public static $prefixesPsr0 = array (
        'A' => 
        array (
            'AlgoliaSearch' => 
            array (
                0 => __DIR__ . '/..' . '/algolia/algoliasearch-client-php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit0f834ea25fa694cc8a9a9ea02e9ac932::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
