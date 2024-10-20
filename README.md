# Filament Reporting

### Reporting for Filament with advanced functionality

This package provides a Filament resource to add advanced functionality for filtering and reporting data from tables.

## Requirements

-   Laravel v11
-   Filament v3
-   SpartanNL/Laravel-Excel v3
-   Ariaieboy/Filament-jalali-datetime V1
-   Ariaieboy/Filament-jalali-datetimepicker V3
-   Hekmatinasser/Verta V8

## Languages Supported

Filament Reporting Plugin is translated for :

-   us English
-   fa Farsi

## Installation

You can install the package via composer:

```bash
composer require iracode-com/filament-reporting
```

After that run the install command:

```bash
php artisan filament-reporting:install
```

This will publish the config & migrations & translations from `iracode-com/filament-reporting`

And run migrates

```bash
php artisan migrate
```

You can manually publish the configuration file with:

```bash
php artisan vendor:publish --tag="filament-reporting-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * This model will be used to report.
     */
    'import_model' => \IracodeCom\FilamentReporting\Models\Report::class,

    /*
     * This model will be determined as user model.
     * creator, updater: foreign key columns in report model for creator, updater relationships
     */
    'user'         => [
        'model'   => \App\Models\User::class,
        'creator' => 'created_by',
        'updater' => 'updated_by'
    ],

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Import model shipped with this package.
     */
    'table'        => 'ir_reports',

    'resources' => [
        'label'                  => 'Reporting',
        'plural_label'           => 'Reportings',
        'navigation_group'       => null,
        'navigation_icon'        => 'heroicon-o-clipboard-document-check',
        'navigation_sort'        => null,
        'navigation_count_badge' => false,
        'resource'               => \IracodeCom\FilamentReporting\Resources\ReportResource::class,
    ],

    'datetime_format' => 'd/m/Y H:i:s',
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-reporting-views"
```

## Usage

### Basic SpartanNL Laravel Excel usage

In your `AppServiceProvider` add `HeadingRowFormatter::default('none')` method to disable formatting

```php
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

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
        HeadingRowFormatter::default('none');
    }
}
```

## Plugin usage

In your Panel ServiceProvider `(App\Providers\Filament)` active the plugin

Add the `IracodeCom\FilamentReporting\FilamentReportingPlugin` to your panel config

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make(),
        ]);
}
```

## Customising the ReportResource

You can swap out the `ReportResource` used by updating the `->resource()` value. Use this to create your own `CustomResource` class and extend the original at `\IracodeCom\FilamentReporting\Resources\ReportResource::class`. This will allow you to customise everything such as the views, table, form and permissions.

> [!NOTE]
> If you wish to change the resource on List and View page be sure to replace the `getPages` method on the new resource and create your own version of the `ListPage` and `ViewPage` classes to reference the custom `CustomResource`.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->resource(\Path\For\Your\CustomResource::class),
        ]);
}
```

## Customising label Resource

You can swap out the `Resource label` used by updating the `->label()` and `->pluralLabel()` value.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->label('Reporting')
                ->pluralLabel('Reportings'),
        ]);
}
```

## Grouping resource navigation items

You can add a `Resource navigation group` updating the `->navigationGroup()` value.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->navigationGroup('Reporting'),
        ]);
}
```

## Customising a resource navigation icon

You can swap out the `Resource navigation icon` used by updating the `->navigationIcon()` value.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->navigationIcon('heroicon-o-clipboard-document-check'),
        ]);
}
```

## Active a count badge

You can active `Count Badge` updating the `->navigationCountBadge()` value.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->navigationCountBadge(true),
        ]);
}
```

## Set navigation sort

You can set the `Resource navigation sort` used by updating the `->navigationSort()` value.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->navigationSort(3),
        ]);
}
```

## Authorization

If you would like to prevent certain users from accessing the logs resource, you should add a authorize callback in the `FilamentReportingPlugin` chain.

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->authorize(
                    fn () => auth()->user()->id === 1
                ),
        ]);
}
```

## Full configuration

```php
use IracodeCom\FilamentReporting\FilamentReportingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportingPlugin::make()
                ->resource(\Path\For\Your\CustomResource::class)
                ->label('Reporting')
                ->pluralLabel('Reportings')
                ->navigationGroup('Reporting')
                ->navigationIcon('heroicon-o-clipboard-document-check')
                ->navigationCountBadge(true)
                ->navigationSort(2)
                ->authorize(
                    fn () => auth()->user()->id === 1
                ),
        ]);
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Acknowledgements

Special acknowledgment goes to these remarkable tools and people (developers), the Reporting plugin only exists due to the inspiration and at some point the use of these people's codes.

-   [Filament](https://github.com/filamentphp/filament)

## Credits

-   [ArdavanShamroshan](.com/Ardavan-Shamroshan)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
