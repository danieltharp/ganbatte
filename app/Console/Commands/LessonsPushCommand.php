<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\LessonContentSeeder;
use Illuminate\Support\Facades\Artisan;

class LessonsPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lessons:push 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the database with the latest lesson content from JSON files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN MODE - No changes will be made to the database');
            $this->info('This would update the database with content from: resources/data/');
            $this->line('');
            
            // Show what files would be processed
            $this->showFilesToProcess();
            return 0;
        }

        // Show intro
        $this->info('🚀 Ganbatte Lessons Push');
        $this->info('This will update your database with the latest content from JSON files.');
        $this->line('');

        // Confirmation unless forced
        if (!$this->option('force')) {
            if (!$this->confirm('Are you ready to update the database with the latest lesson content?', true)) {
                $this->info('Operation cancelled.');
                return 0;
            }
            $this->line('');
        }

        // Run the seeder
        $this->info('📚 Importing lesson content...');
        $this->line('');
        
        try {
            $seeder = new LessonContentSeeder();
            $seeder->setCommand($this);
            $seeder->run();
            
            $this->line('');
            $this->info('✅ Lesson content successfully updated!');
            $this->line('');
            $this->info('💡 Tips:');
            $this->line('  • Use --dry-run to preview changes before applying');
            $this->line('  • Use --force to skip confirmation prompt');
            $this->line('  • Check the workflow guide: docs/content-update-workflow.md');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to update lesson content:');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Show what files would be processed in dry-run mode
     */
    private function showFilesToProcess()
    {
        $dataPath = resource_path('data');
        $contentTypes = ['lessons', 'vocabulary', 'grammar', 'questions', 'tests', 'worksheets', 'exercises', 'sections', 'pages'];
        
        $this->info('Files that would be processed:');
        $this->line('');
        
        foreach ($contentTypes as $type) {
            $files = glob("{$dataPath}/{$type}/lesson-*.json");
            if (!empty($files)) {
                $this->line("📁 {$type}/");
                foreach ($files as $file) {
                    $filename = basename($file);
                    $size = number_format(filesize($file));
                    $this->line("  └─ {$filename} ({$size} bytes)");
                }
                $this->line('');
            }
        }
        
        if (empty(glob("{$dataPath}/lessons/lesson-*.json"))) {
            $this->warn('⚠️  No lesson files found in resources/data/lessons/');
        }
    }
} 