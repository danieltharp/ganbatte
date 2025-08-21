<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Vocabulary;
use App\Models\Lesson;
use App\Models\GrammarPoint;
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
     * Show contributions management interface for staff
     */
    public function manage(Request $request)
    {
        $query = Contribution::with(['user', 'reviewer', 'lesson'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by lesson if provided
        if ($request->has('lesson_id') && $request->lesson_id !== '') {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Filter by object type if provided
        if ($request->has('object_type') && $request->object_type !== '') {
            $query->where('object_type', $request->object_type);
        }

        $contributions = $query->paginate(25);

        // Get filter options
        $lessons = Lesson::orderBy('chapter')->get();
        $objectTypes = Contribution::select('object_type')
            ->distinct()
            ->orderBy('object_type')
            ->pluck('object_type');

        return view('contribute.manage', compact('contributions', 'lessons', 'objectTypes'));
    }

    /**
     * Update contribution status (accept/reject)
     */
    public function updateStatus(Request $request, Contribution $contribution): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        if (!$user->canManageContributions()) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:accepted,completed',
        ]);

        try {
            if ($validated['status'] === 'accepted') {
                $contribution->markAsAccepted($user);
                $message = 'Contribution marked as accepted.';
            } else {
                $contribution->markAsCompleted();
                $message = 'Contribution marked as completed.';
            }

            return response()->json([
                'message' => $message,
                'contribution' => $contribution->load(['user', 'reviewer'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update contribution status.'
            ], 500);
        }
    }

    /**
     * Show individual contribution with JSON preview
     */
    public function show(Contribution $contribution)
    {
        if (!Auth::check() || !Auth::user()->canManageContributions()) {
            abort(403, 'Insufficient permissions to view contributions.');
        }

        // Load the actual object being contributed to
        $targetObject = $this->loadTargetObject($contribution->object_type, $contribution->object_id);
        
        if (!$targetObject) {
            abort(404, 'Target object not found.');
        }

        // Get the available fields for this object type
        $availableFields = $this->getAvailableFieldsForObject($contribution->object_type);
        
        // Transform object to match lesson JSON format
        $jsonObject = $this->transformToJsonFormat($contribution->object_type, $targetObject);

        return view('contribute.show', compact('contribution', 'targetObject', 'availableFields', 'jsonObject'));
    }

    /**
     * Delete/reject a contribution
     */
    public function destroy(Contribution $contribution): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        if (!$user->canManageContributions()) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        try {
            // Handle different deletion scenarios
            if ($contribution->isNew()) {
                // Rejecting a new contribution - increment rejected count
                $contribution->markAsRejected($user);
                return response()->json(['message' => 'Contribution rejected and removed.']);
            } else {
                // Cleaning up completed contribution - just delete without stat impact
                $contribution->delete();
                return response()->json(['message' => 'Contribution removed from queue.']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove contribution.'], 500);
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

    /**
     * Load the target object for contribution preview
     */
    private function loadTargetObject(string $objectType, string $objectId)
    {
        switch ($objectType) {
            case 'vocabulary':
                return Vocabulary::with('lesson')->find($objectId);
            case 'lesson':
                return Lesson::find($objectId);
            case 'grammar_point':
                return GrammarPoint::find($objectId);
            default:
                return null;
        }
    }

    /**
     * Get available JSON fields for an object type
     */
    private function getAvailableFieldsForObject(string $objectType): array
    {
        switch ($objectType) {
            case 'vocabulary':
                return [
                    'word.japanese' => 'Japanese Word',
                    'word.furigana' => 'Furigana',
                    'word.english' => 'English Translation',
                    'part_of_speech' => 'Part of Speech (array)',
                    'verb_type' => 'Verb Type',
                    'adjective_type' => 'Adjective Type',
                    'jlpt_level' => 'JLPT Level',
                    'frequency_rank' => 'Frequency Rank',
                    'pitch_accent' => 'Pitch Accent',
                    'mnemonics' => 'Mnemonics',
                    'example_sentences' => 'Example Sentences (array)',
                    'audio.filename' => 'Audio Filename',
                    'audio.speaker' => 'Audio Speaker',
                    'related_words' => 'Related Words (array)',
                    'tags' => 'Tags (array)',
                    'include_in_kanji_worksheet' => 'Include in Kanji Worksheet (boolean)'
                ];

            case 'lesson':
                return [
                    'title_japanese' => 'Japanese Title',
                    'title_furigana' => 'Title Furigana',
                    'title_english' => 'English Title',
                    'description' => 'Description',
                    'difficulty' => 'Difficulty',
                    'estimated_time_minutes' => 'Estimated Time (minutes)',
                    'prerequisites' => 'Prerequisites (array)'
                ];

            default:
                return [];
        }
    }

    /**
     * Transform database object to lesson JSON format
     */
    private function transformToJsonFormat(string $objectType, $object): array
    {
        switch ($objectType) {
            case 'vocabulary':
                $json = [
                    'id' => $object->id,
                    'lesson_id' => $object->lesson_id,
                    'word' => [
                        'japanese' => $object->word_japanese,
                        'english' => $object->word_english
                    ]
                ];

                // Add optional word fields
                if ($object->word_furigana) {
                    $json['word']['furigana'] = $object->word_furigana;
                }

                // Add other fields only if they have values
                if ($object->part_of_speech && is_array($object->part_of_speech) && count($object->part_of_speech) > 0) {
                    $json['part_of_speech'] = $object->part_of_speech;
                }

                if ($object->verb_type) {
                    $json['verb_type'] = $object->verb_type;
                }

                if ($object->adjective_type) {
                    $json['adjective_type'] = $object->adjective_type;
                }

                if ($object->conjugations && is_array($object->conjugations) && count($object->conjugations) > 0) {
                    $json['conjugations'] = $object->conjugations;
                }

                if ($object->pitch_accent) {
                    $json['pitch_accent'] = $object->pitch_accent;
                }

                if ($object->jlpt_level) {
                    $json['jlpt_level'] = $object->jlpt_level;
                }

                if ($object->frequency_rank) {
                    $json['frequency_rank'] = $object->frequency_rank;
                }

                if ($object->example_sentences && is_array($object->example_sentences) && count($object->example_sentences) > 0) {
                    $json['example_sentences'] = $object->example_sentences;
                }

                // Audio object
                if ($object->audio_filename || $object->audio_speaker) {
                    $json['audio'] = [];
                    if ($object->audio_filename) {
                        $json['audio']['filename'] = $object->audio_filename;
                    }
                    if ($object->audio_duration) {
                        $json['audio']['duration'] = $object->audio_duration;
                    }
                    if ($object->audio_speaker) {
                        $json['audio']['speaker'] = $object->audio_speaker;
                    }
                }

                if ($object->mnemonics) {
                    $json['mnemonics'] = $object->mnemonics;
                }

                if ($object->related_words && is_array($object->related_words) && count($object->related_words) > 0) {
                    $json['related_words'] = $object->related_words;
                }

                if ($object->tags && is_array($object->tags) && count($object->tags) > 0) {
                    $json['tags'] = $object->tags;
                }

                if ($object->also_accepted && is_array($object->also_accepted) && count($object->also_accepted) > 0) {
                    $json['also_accepted'] = $object->also_accepted;
                }

                if ($object->include_in_kanji_worksheet) {
                    $json['include_in_kanji_worksheet'] = $object->include_in_kanji_worksheet;
                }

                return $json;

            case 'lesson':
                $json = [
                    'id' => $object->id,
                    'chapter' => $object->chapter,
                    'title_english' => $object->title_english
                ];

                if ($object->title_japanese) {
                    $json['title_japanese'] = $object->title_japanese;
                }

                if ($object->title_furigana) {
                    $json['title_furigana'] = $object->title_furigana;
                }

                if ($object->description) {
                    $json['description'] = $object->description;
                }

                if ($object->difficulty) {
                    $json['difficulty'] = $object->difficulty;
                }

                if ($object->estimated_time_minutes) {
                    $json['estimated_time_minutes'] = $object->estimated_time_minutes;
                }

                if ($object->prerequisites && is_array($object->prerequisites) && count($object->prerequisites) > 0) {
                    $json['prerequisites'] = $object->prerequisites;
                }

                return $json;

            default:
                return [];
        }
    }
}
