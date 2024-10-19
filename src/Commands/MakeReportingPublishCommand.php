<?php

namespace IracodeCom\FilamentReporting\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;

class MakeReportingPublishCommand extends Command
{
    use Concerns\CanManipulateFiles;

    public $signature = 'filament-reporting:publish';

    public $description = "Publish filament report's Resource.";

    public function handle(Filesystem $filesystem): int
    {
        $baseResourcePath       = app_path((string) Str::of('Filament\\Resources')->replace('\\', '/'));
        $reportResourcePath = app_path((string) Str::of('Filament\\Resources\\ReportResource.php')->replace('\\', '/'));

        if ($this->checkForCollision([$reportResourcePath])) {
            $confirmed = confirm('Report Resource already exists. Overwrite?');
            if (! $confirmed) {
                return self::INVALID;
            }
        }

        $filesystem->ensureDirectoryExists($baseResourcePath);
        $filesystem->copyDirectory(__DIR__ . '/../Resources', $baseResourcePath);

        $currentNamespace = 'IracodeCom\\FilamentReporting\\Resources';
        $newNamespace     = 'App\\Filament\\Resources';

        $this->replaceInFile($reportResourcePath, $currentNamespace, $newNamespace);
        $this->replaceInFile($baseResourcePath . '/ReportResource/Pages/CreateReport.php', $currentNamespace, $newNamespace);
        $this->replaceInFile($baseResourcePath . '/ReportResource/Pages/EditReport.php', $currentNamespace, $newNamespace);
        $this->replaceInFile($baseResourcePath . '/ReportResource/Pages/Reporting.php', $currentNamespace, $newNamespace);
        $this->replaceInFile($baseResourcePath . '/ReportResource/Pages/ListReports.php', $currentNamespace, $newNamespace);

        $this->components->info("Report's Resource have been published successfully!");

        return self::SUCCESS;
    }
}
