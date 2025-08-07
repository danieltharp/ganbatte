<?php

namespace App\Console\Commands;

use App\Models\GrammarPoint;
use Illuminate\Console\Command;

class ListGrammarWithoutExplanations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'grammar:list-without-explanations {--lesson= : Filter by lesson ID}';

    /**
     * The console command description.
     */
    protected $description = 'List grammar points that do not have markdown explanations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = GrammarPoint::with('lesson');
        
        // Filter by lesson if provided
        if ($this->option('lesson')) {
            $query->where('lesson_id', $this->option('lesson'));
        }
        
        $grammarPoints = $query->orderBy('lesson_id')->get();
        
        $withoutMarkdown = $grammarPoints->reject(function ($grammarPoint) {
            return $grammarPoint->hasMarkdownExplanation();
        });
        
        $withMarkdown = $grammarPoints->filter(function ($grammarPoint) {
            return $grammarPoint->hasMarkdownExplanation();
        });
        
        $this->info("📊 Grammar Points Status Report");
        $this->line("─────────────────────────────────");
        
        if ($this->option('lesson')) {
            $this->line("🎯 Filtered by lesson: " . $this->option('lesson'));
        }
        
        $this->line("📝 With Markdown: " . $withMarkdown->count());
        $this->line("❌ Without Markdown: " . $withoutMarkdown->count());
        $this->line("📊 Total: " . $grammarPoints->count());
        $this->newLine();
        
        if ($withoutMarkdown->isEmpty()) {
            $this->success("🎉 All grammar points have markdown explanations!");
            return 0;
        }
        
        $this->warn("📋 Grammar Points Without Markdown Explanations:");
        $this->newLine();
        
        $headers = ['ID', 'Lesson', 'English Name', 'Pattern', 'Has Plain Text'];
        $rows = [];
        
        foreach ($withoutMarkdown as $grammarPoint) {
            $rows[] = [
                $grammarPoint->id,
                $grammarPoint->lesson ? "Lesson {$grammarPoint->lesson->chapter}" : 'No lesson',
                $grammarPoint->name_english,
                \Str::limit($grammarPoint->pattern, 30),
                $grammarPoint->explanation ? '✓' : '✗'
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->newLine();
        $this->info("💡 To create a markdown explanation for a grammar point, run:");
        $this->line("   php artisan grammar:explanation <grammar_id>");
        
        // Offer to create explanations interactively
        if ($this->confirm('Would you like to create markdown explanations now?')) {
            foreach ($withoutMarkdown as $grammarPoint) {
                $this->newLine();
                $this->line("🎯 Grammar Point: {$grammarPoint->name_english}");
                $this->line("📝 Pattern: {$grammarPoint->pattern}");
                
                if ($this->confirm("Create markdown explanation for '{$grammarPoint->name_english}'?")) {
                    $this->call('grammar:explanation', ['grammar_id' => $grammarPoint->id]);
                }
            }
        }
        
        return 0;
    }
}
