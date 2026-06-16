<?php

namespace App\Providers;

use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\Local\LocalFilesystemAdapter as FlysystemLocalAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Http/helpers.php');
        require_once app_path('Http/constants.php');

        if (!extension_loaded('fileinfo')) {
            Storage::extend('local', function ($app, array $config) {
                $visibility = PortableVisibilityConverter::fromArray(
                    $config['permissions'] ?? [],
                    $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE
                );

                $links = ($config['links'] ?? null) === 'skip'
                    ? FlysystemLocalAdapter::SKIP_LINKS
                    : FlysystemLocalAdapter::DISALLOW_LINKS;

                $adapter = new FlysystemLocalAdapter(
                    $config['root'],
                    $visibility,
                    $config['lock'] ?? LOCK_EX,
                    $links,
                    new ExtensionMimeTypeDetector(),
                );

                $flysystem = new FlysystemFilesystem($adapter, Arr::only($config, [
                    'directory_visibility',
                    'disable_asserts',
                    'retain_visibility',
                    'temporary_url',
                    'url',
                    'visibility',
                ]));

                return new LocalFilesystemAdapter($flysystem, $adapter, $config);
            });
        }
    }
}
