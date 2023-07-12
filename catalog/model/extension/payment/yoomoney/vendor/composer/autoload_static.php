<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit342018ee5eebabc97e50ec6b5975cddf
{
    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'YooKassa\\' => 9,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'YooKassa\\' => 
        array (
            0 => __DIR__ . '/..' . '/yoomoney/yookassa-sdk-php/lib',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit342018ee5eebabc97e50ec6b5975cddf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit342018ee5eebabc97e50ec6b5975cddf::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
