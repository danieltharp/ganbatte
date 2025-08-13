<?php

namespace App\Http\Controllers;

use App\Models\Worksheet;
use App\Models\Vocabulary;
use App\Models\Lesson;
use App\Helpers\KanjiSvgHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Worksheet::with('lesson');
        
        // Filter by published status if provided
        if ($request->has('published') && $request->published !== '') {
            if ($request->published === '1') {
                $query->published();
            }
        }
        
        // Filter by lesson if provided
        if ($request->has('lesson_id') && $request->lesson_id != '') {
            $query->where('lesson_id', $request->lesson_id);
        }
        
        // Filter by type if provided
        if ($request->has('type') && $request->type != '') {
            $query->byType($request->type);
        }
        
        $worksheets = $query->orderBy('created_at', 'desc')->get();
        $lessons = Lesson::orderBy('chapter')->get();
        
        return view('worksheets.index', compact('worksheets', 'lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lessons = Lesson::orderBy('chapter')->get();
        $worksheetTypes = Worksheet::WORKSHEET_TYPES;
        
        return view('worksheets.create', compact('lessons', 'worksheetTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', Worksheet::WORKSHEET_TYPES),
            'lesson_id' => 'required|exists:lessons,id',
            'content_ids' => 'nullable|array',
            'template' => 'nullable|string',
        ]);

        $worksheet = Worksheet::create($validated);

        return redirect()->route('worksheets.show', $worksheet)
            ->with('success', 'Worksheet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Worksheet $worksheet)
    {
        $worksheet->load(['lesson', 'vocabulary', 'grammarPoints', 'questions']);
        
        // Get kanji coverage statistics if it's a kanji practice worksheet
        $kanjiStats = null;
        if ($worksheet->type === 'kanji_practice') {
            if (!empty($worksheet->content_ids)) {
                // Use content_ids if populated
                $vocabulary = \App\Models\Vocabulary::whereIn('id', $worksheet->content_ids)
                    ->forKanjiWorksheet()
                    ->get();
            } else {
                // Fall back to relationship
                $vocabulary = $worksheet->vocabulary()->forKanjiWorksheet()->get();
            }
            $kanjiStats = KanjiSvgHelper::getKanjiCoverageStats($vocabulary);
        }
        
        return view('worksheets.show', compact('worksheet', 'kanjiStats'));
    }

    /**
     * Show the worksheet generator form
     */
    public function generate(Worksheet $worksheet)
    {
        $worksheet->load(['lesson', 'vocabulary']);
        
        // Get vocabulary items suitable for kanji worksheets
        if (!empty($worksheet->content_ids)) {
            // Use content_ids if populated
            $vocabulary = \App\Models\Vocabulary::whereIn('id', $worksheet->content_ids)
                ->forKanjiWorksheet()
                ->get();
        } else {
            // Fall back to relationship
            $vocabulary = $worksheet->vocabulary()->forKanjiWorksheet()->get();
        }
        
        // Get kanji statistics
        $kanjiStats = KanjiSvgHelper::getKanjiCoverageStats($vocabulary);
        
        return view('worksheets.generate', compact('worksheet', 'vocabulary', 'kanjiStats'));
    }

    /**
     * Generate and download kanji practice PDF
     */
    public function generateKanjiPdf(Request $request, Worksheet $worksheet)
    {
        // Validate request
        $validated = $request->validate([
            'paper_size' => 'required|in:A4,Letter,Legal',
            'orientation' => 'required|in:portrait,landscape',
            'grid_size' => 'nullable|integer|min:1|max:20',
            'include_stroke_order' => 'boolean',
            'include_readings' => 'boolean',
            'vocabulary_ids' => 'nullable|array',
            'vocabulary_ids.*' => 'exists:vocabulary,id',
        ]);

        // Get vocabulary items
        if (!empty($worksheet->content_ids)) {
            // Use content_ids if populated
            $vocabularyQuery = \App\Models\Vocabulary::whereIn('id', $worksheet->content_ids)
                ->forKanjiWorksheet();
            
            if (!empty($validated['vocabulary_ids'])) {
                $vocabularyQuery->whereIn('id', $validated['vocabulary_ids']);
            }
        } else {
            // Fall back to relationship
            $vocabularyQuery = $worksheet->vocabulary()->forKanjiWorksheet();
            
            if (!empty($validated['vocabulary_ids'])) {
                $vocabularyQuery->whereIn('vocabulary.id', $validated['vocabulary_ids']);
            }
        }
        
        $vocabulary = $vocabularyQuery->get();

        if ($vocabulary->isEmpty()) {
            return back()->with('error', 'No vocabulary items selected for worksheet generation.');
        }

        // Prepare kanji data for template
        $kanjiData = $vocabulary->map(function ($vocab) use ($validated) {
            $kanjiInfo = KanjiSvgHelper::getKanjiDataForVocabulary($vocab);
            
            return [
                'vocabulary' => $vocab,
                'kanji' => $kanjiInfo,
                'include_stroke_order' => $validated['include_stroke_order'] ?? true,
                'include_readings' => $validated['include_readings'] ?? true,
            ];
        })->filter(function ($item) {
            // Only include items that have at least one available kanji SVG
            return collect($item['kanji'])->some(function($kanji) {
                return $kanji['svg_available'];
            });
        });

        if ($kanjiData->isEmpty()) {
            return back()->with('error', 'No kanji SVG files found for the selected vocabulary.');
        }

        // Prepare settings
        $settings = [
            'paper_size' => $validated['paper_size'],
            'orientation' => $validated['orientation'],
            'grid_size' => $validated['grid_size'] ?? 6,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('worksheets.templates.kanji-practice', compact('kanjiData', 'settings', 'worksheet'));
        $pdf->setPaper($settings['paper_size'], $settings['orientation']);
        
        // Configure for better Unicode support
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);  // Enable for Google Fonts
        $pdf->setOption('defaultFont', 'courier');  // Fallback font
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('isPhpEnabled', true);

        $filename = sprintf(
            'kanji-worksheet-%s-%s.pdf',
            $worksheet->lesson->chapter ?? 'custom',
            now()->format('Y-m-d')
        );

        return $pdf->stream($filename);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Worksheet $worksheet)
    {
        $lessons = Lesson::orderBy('chapter')->get();
        $worksheetTypes = Worksheet::WORKSHEET_TYPES;
        
        return view('worksheets.edit', compact('worksheet', 'lessons', 'worksheetTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Worksheet $worksheet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', Worksheet::WORKSHEET_TYPES),
            'lesson_id' => 'required|exists:lessons,id',
            'content_ids' => 'nullable|array',
            'template' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $worksheet->update($validated);

        return redirect()->route('worksheets.show', $worksheet)
            ->with('success', 'Worksheet updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Worksheet $worksheet)
    {
        $worksheet->delete();

        return redirect()->route('worksheets.index')
            ->with('success', 'Worksheet deleted successfully.');
    }
} 