<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Vocabulary;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContributeController extends Controller
{
    /**
     * Show the contribute tools index page
     */
    public function index()
    {
        return view('contribute.index');
    }

    /**
     * Show the vocabulary JSON generator
     */
    public function vocabularyGenerator()
    {
        return view('contribute.vocabulary.generator');
    }

    /**
     * Store a new contribution
     */
    public function store(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $validated = $request->validate([
            'lesson_id' => 'required|string|exists:lessons,id',
            'object_type' => 'required|string|max:50',
            'object_id' => 'required|string|max:255',
            'field_type' => 'nullable|string|max:100',
            'contribution_text' => 'required|string|max:2000',
        ]);

        try {
            $contribution = Contribution::create([
                'lesson_id' => $validated['lesson_id'],
                'user_id' => Auth::id(),
                'object_type' => $validated['object_type'],
                'object_id' => $validated['object_id'],
                'field_type' => $validated['field_type'],
                'contribution_text' => trim($validated['contribution_text']),
                'status' => Contribution::STATUS_NEW,
            ]);

            return response()->json([
                'message' => 'Contribution submitted successfully! Thank you for helping improve the content.',
                'contribution_id' => $contribution->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to submit contribution. Please try again.'
            ], 500);
        }
    }

    /**
     * Get contribution options for a specific object
     */
    public function getContributionOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'object_type' => 'required|string',
            'object_id' => 'required|string',
        ]);

        $options = $this->getFieldTypesForObjectType($validated['object_type']);

        // Get the object details for context
        $objectDetails = $this->getObjectDetails($validated['object_type'], $validated['object_id']);

        return response()->json([
            'object_type' => $validated['object_type'],
            'object_id' => $validated['object_id'],
            'object_details' => $objectDetails,
            'field_options' => $options
        ]);
    }

    /**
     * Get available field types for a given object type
     */
    private function getFieldTypesForObjectType(string $objectType): array
    {
        switch ($objectType) {
            case 'vocabulary':
                return [
                    'mnemonic' => 'Memory Aid / Mnemonic',
                    'example_sentence' => 'Example Sentence',
                    'pronunciation_note' => 'Pronunciation Note',
                    'usage_note' => 'Usage Note',
                    'alternative_meaning' => 'Alternative Meaning',
                    'related_word' => 'Related Word',
                    'correction' => 'General Correction',
                    'other' => 'Other Suggestion'
                ];

            case 'lesson':
                return [
                    'explanation' => 'Explanation Improvement',
                    'example' => 'Additional Example',
                    'clarification' => 'Clarification',
                    'correction' => 'Correction',
                    'other' => 'Other Suggestion'
                ];

            case 'grammar_point':
                return [
                    'explanation' => 'Explanation Improvement',
                    'example_sentence' => 'Example Sentence',
                    'usage_note' => 'Usage Note',
                    'correction' => 'Correction',
                    'other' => 'Other Suggestion'
                ];

            default:
                return [
                    'suggestion' => 'Suggestion',
                    'correction' => 'Correction',
                    'other' => 'Other'
                ];
        }
    }

    /**
     * Get object details for context display
     */
    private function getObjectDetails(string $objectType, string $objectId): ?array
    {
        switch ($objectType) {
            case 'vocabulary':
                $vocab = Vocabulary::with('lesson')->find($objectId);
                if (!$vocab) return null;
                
                return [
                    'title' => $vocab->word_japanese . ' - ' . $vocab->word_english,
                    'subtitle' => 'Vocabulary from ' . ($vocab->lesson->title_english ?? 'Lesson'),
                    'lesson_id' => $vocab->lesson_id
                ];

            case 'lesson':
                $lesson = Lesson::find($objectId);
                if (!$lesson) return null;
                
                return [
                    'title' => $lesson->title_english,
                    'subtitle' => 'Lesson ' . $lesson->chapter,
                    'lesson_id' => $lesson->id
                ];

            default:
                return [
                    'title' => ucfirst($objectType) . ': ' . $objectId,
                    'subtitle' => 'Contribution',
                    'lesson_id' => null
                ];
        }
    }
}
