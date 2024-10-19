<?php

namespace IracodeCom\FilamentReporting;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use IracodeCom\FilamentReporting\Commands\MakeReportingPublishCommand;

class FilamentReportingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-reporting')
            ->hasViews('filament-reporting')
            ->hasMigration('create_ir_reports_table')
            ->hasConfigFile()
            ->hasTranslations()
            ->runsMigrations()
            ->hasCommands(MakeReportingPublishCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(fn(InstallCommand $command) => $command->info('Hello, and welcome to the filament reporting package!'))
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('iracode-com/filament-reporting')
                    ->startWith(function (InstallCommand $command) {
                        $command->info('Enjoy exploring new reporting experience!');
                    });
            });
    }
}