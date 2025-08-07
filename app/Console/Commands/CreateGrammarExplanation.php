<?php

namespace App\Console\Commands;

use App\Models\GrammarPoint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateGrammarExplanation extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'grammar:explanation {grammar_id : The ID of the grammar point}';

    /**
     * The console command description.
     */
    protected $description = 'Create a markdown explanation file for a grammar point';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $grammarId = $this->argument('grammar_id');
        
        // Find the grammar point
        $grammarPoint = GrammarPoint::find($grammarId);
        if (!$grammarPoint) {
            $this->error("Grammar point with ID '{$grammarId}' not found.");
            return 1;
        }

        $filePath = $grammarPoint->getMarkdownExplanationPath();
        
        // Check if file already exists
        if (File::exists($filePath)) {
            if (!$this->confirm("Markdown explanation already exists for '{$grammarPoint->name_english}'. Overwrite?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Create the directory if it doesn't exist
        $directory = dirname($filePath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Generate template content
        $template = $this->generateTemplate($grammarPoint);
        
        // Write the file
        File::put($filePath, $template);
        
        $this->info("✅ Created markdown explanation file:");
        $this->line("   📁 {$filePath}");
        $this->line("   🌐 Grammar Point: {$grammarPoint->name_english}");
        $this->line("   📝 Pattern: {$grammarPoint->pattern}");
        
        if ($this->confirm('Open the file for editing?', true)) {
            $editor = env('EDITOR', 'nano');
            system("{$editor} " . escapeshellarg($filePath));
        }
        
        return 0;
    }

    /**
     * Generate a markdown template for the grammar point
     */
    private function generateTemplate(GrammarPoint $grammarPoint): string
    {
        $template = "# {$grammarPoint->name_english}\n\n";
        
        if ($grammarPoint->name_japanese) {
            $template .= "**Japanese**: {$grammarPoint->name_japanese}\n\n";
        }
        
        $template .= "## Pattern\n\n";
        $template .= "```\n{$grammarPoint->pattern}\n```\n\n";
        
        $template .= "## Usage\n\n";
        if ($grammarPoint->usage) {
            $template .= "{$grammarPoint->usage}\n\n";
        } else {
            $template .= "*Add usage description here...*\n\n";
        }
        
        $template .= "## Explanation\n\n";
        if ($grammarPoint->explanation) {
            $template .= "{$grammarPoint->explanation}\n\n";
        } else {
            $template .= "*Add detailed explanation here...*\n\n";
        }
        
        $template .= "## Key Points\n\n";
        $template .= "- **Point 1**: *Add key learning point*\n";
        $template .= "- **Point 2**: *Add key learning point*\n";
        $template .= "- **Point 3**: *Add key learning point*\n\n";
        
        $template .= "## Formation\n\n";
        $template .= "*Describe how to form this grammar pattern...*\n\n";
        
        $template .= "## Usage Notes\n\n";
        $template .= "> **Important**: *Add important usage notes or warnings here*\n\n";
        
        if ($grammarPoint->jlpt_level) {
            $template .= "## JLPT Level\n\n";
            $template .= "**{$grammarPoint->jlpt_level}** - *Add level-specific notes if needed*\n\n";
        }
        
        $template .= "## Related Grammar\n\n";
        $template .= "*Link to related grammar patterns...*\n\n";
        
        $template .= "---\n\n";
        $template .= "*Add any additional notes or cultural context here.*\n";
        
        return $template;
    }
}
