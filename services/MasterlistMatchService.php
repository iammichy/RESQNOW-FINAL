<?php

namespace App\Services;

use PDO;

/**
 * MasterlistMatchService
 * Fuzzy-matches a registration name against the resident_masterlist.
 * Used during resident registration to flag potential matches.
 */
class MasterlistMatchService
{
    private PDO $db;

    // Weights for scoring: exact > normalized > word overlap > soundex
    private const EXACT_SCORE     = 100;
    private const NORMALIZED_SCORE = 90;
    private const FUZZY_THRESHOLD  = 70; // minimum score to count as a match
    private const MAX_RESULTS      = 5;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Main entry point: find masterlist matches for a given name.
     * Returns ['match_type', 'matches', 'best_match', 'confidence']
     */
    public function findMatches(string $name): array
    {
        $candidates = $this->db->query(
            "SELECT id, purok, family_no, resident_no, name, status FROM resident_masterlist WHERE status = 'active' ORDER BY purok, family_no, resident_no"
        )->fetchAll();

        $scored = [];
        $normalizedInput = $this->normalize($name);
        $wordsInput = $this->words($normalizedInput);

        foreach ($candidates as $c) {
            $score = $this->score($normalizedInput, $wordsInput, $this->normalize($c['name']));
            if ($score >= self::FUZZY_THRESHOLD) {
                $scored[] = array_merge($c, ['score' => $score]);
            }
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $top = array_slice($scored, 0, self::MAX_RESULTS);

        if (empty($top)) {
            return [
                'match_type'  => 'not_found',
                'matches'     => [],
                'best_match'  => null,
                'confidence'  => 0,
            ];
        }

        // Check if the best match is already registered
        $bestId = $top[0]['id'];
        $existing = $this->db->prepare(
            "SELECT u.id, u.name, u.status FROM users u WHERE u.masterlist_id = :mid AND u.role = 'resident' LIMIT 1"
        );
        $existing->execute(['mid' => $bestId]);
        $existingUser = $existing->fetch();

        if ($existingUser) {
            return [
                'match_type'  => 'flagged_duplicate',
                'matches'     => $top,
                'best_match'  => $top[0],
                'confidence'  => $top[0]['score'],
                'existing_user' => $existingUser,
            ];
        }

        if (count($top) > 1 && $top[0]['score'] === $top[1]['score']) {
            $type = 'multiple';
        } elseif ($top[0]['score'] >= self::EXACT_SCORE) {
            $type = 'exact';
        } else {
            $type = 'fuzzy';
        }

        return [
            'match_type'  => $type,
            'matches'     => $top,
            'best_match'  => $top[0],
            'confidence'  => $top[0]['score'],
        ];
    }

    /**
     * Normalize a name for comparison.
     * - lowercase
     * - remove punctuation / Jr. / Sr. / III etc.
     * - trim whitespace
     * - swap "Surname, Firstname" to "Firstname Surname"
     */
    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));

        // Handle "Surname, Firstname Middlename" format
        if (str_contains($name, ',')) {
            $parts = array_map('trim', explode(',', $name, 2));
            $name = $parts[1] . ' ' . $parts[0];
        }

        // Remove suffixes
        $name = preg_replace('/\b(jr\.?|sr\.?|ii+|iii+|iv)\b/i', '', $name);

        // Remove punctuation except spaces
        $name = preg_replace('/[^a-z\s]/', '', $name);

        // Collapse spaces
        return preg_replace('/\s+/', ' ', trim($name));
    }

    private function words(string $normalized): array
    {
        return array_filter(explode(' ', $normalized), fn($w) => strlen($w) > 1);
    }

    /**
     * Score two normalized names. Returns 0-100.
     */
    private function score(string $inputNorm, array $inputWords, string $candidateNorm): int
    {
        // Exact normalized match
        if ($inputNorm === $candidateNorm) return self::EXACT_SCORE;

        $candWords = $this->words($candidateNorm);
        if (empty($inputWords) || empty($candWords)) return 0;

        // Word overlap score
        $commonWords = array_intersect($inputWords, $candWords);
        $totalWords  = max(count($inputWords), count($candWords));
        $overlapScore = $totalWords > 0 ? round(count($commonWords) / $totalWords * 100) : 0;

        // Levenshtein distance score on full strings
        $maxLen = max(strlen($inputNorm), strlen($candidateNorm));
        $lev = levenshtein($inputNorm, $candidateNorm);
        $levScore = $maxLen > 0 ? (int) round((1 - $lev / $maxLen) * 100) : 0;

        // Take weighted average
        $final = (int) round(($overlapScore * 0.6) + ($levScore * 0.4));
        return min(95, $final); // cap at 95 to distinguish from exact match
    }
}
