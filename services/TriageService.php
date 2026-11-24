<?php

namespace App\Services;

class TriageService
{
    public const VULNERABILITY_KEY = 'vulnerability';
    public const MAX_SCORE = 16;
    public const UNSURE_THRESHOLD = 0.5;

    private array $questionSets;

    public function __construct()
    {
        $this->questionSets = $this->buildQuestionSets();
    }

    public function getHazardTypes(): array
    {
        return array_keys($this->questionSets);
    }

    public function getQuestions(string $hazardType): array
    {
        $set = $this->questionSets[$hazardType] ?? $this->questionSets['other'];
        return $set['questions'];
    }

    public function getVulnerabilityQuestion(): array
    {
        return [
            'key'     => self::VULNERABILITY_KEY,
            'text'    => 'Does anyone involved need urgent assistance? (senior citizen, infant, PWD, pregnant, needs evacuation)',
            'weight'  => 1,
            'options' => [
                ['label' => 'No', 'points' => 0],
                ['label' => 'Yes', 'points' => 1],
            ],
        ];
    }

    public function compute(array $answers, string $hazardType): array
    {
        $questions = $this->getQuestions($hazardType);
        $vulnQuestion = $this->getVulnerabilityQuestion();

        $breakdown = [];
        $totalWeighted = 0;
        $unsureWeighted = 0;
        $score = 0;

        foreach ($questions as $q) {
            $ans = $answers[$q['key']] ?? null;
            $isUnsure = ($ans === 'unsure' || $ans === null || $ans === '');
            $pointsScored = 0;
            $answerLabel = 'Unsure';

            if (!$isUnsure) {
                foreach ($q['options'] as $opt) {
                    if (($opt['label'] ?? null) === $ans || ($opt['value'] ?? null) === $ans) {
                        $pointsScored = (int) ($opt['points'] ?? 0);
                        $answerLabel = (string) $opt['label'];
                        break;
                    }
                }
            }

            $weighted = $q['weight'] * $pointsScored;
            $score += $weighted;
            $totalWeighted += $q['weight'] * 4;

            if ($isUnsure) {
                $unsureWeighted += $q['weight'] * 4;
            }

            $breakdown[] = [
                'key'           => $q['key'],
                'text'          => $q['text'],
                'answer_label'  => $answerLabel,
                'weight'        => (int) $q['weight'],
                'points_scored' => $pointsScored,
                'is_unsure'     => $isUnsure,
            ];
        }

        $vulnAns = $answers[self::VULNERABILITY_KEY] ?? null;
        $vulnBonus = 0;
        $vulnLabel = 'No';
        $vulnIsUnsure = false;

        if ($vulnAns === 'unsure' || $vulnAns === null || $vulnAns === '') {
            $vulnIsUnsure = true;
            $vulnLabel = 'Unsure';
            $unsureWeighted += $vulnQuestion['weight'] * 4;
        } elseif ($vulnAns === 'Yes' || $vulnAns === 'yes' || $vulnAns === '1') {
            $vulnBonus = 2;
            $vulnLabel = 'Yes (+2 priority bonus)';
        }

        $totalWeighted += $vulnQuestion['weight'] * 4;
        $score += $vulnBonus;

        if ($score > self::MAX_SCORE) {
            $score = self::MAX_SCORE;
        }

        $unsureRatio = $totalWeighted > 0 ? $unsureWeighted / $totalWeighted : 0.0;

        if ($unsureRatio >= self::UNSURE_THRESHOLD) {
            $level = 'pending_reassessment';
        } elseif ($score >= 13) {
            $level = 'critical';
        } elseif ($score >= 9) {
            $level = 'high';
        } elseif ($score >= 5) {
            $level = 'moderate';
        } elseif ($score >= 1) {
            $level = 'low';
        } else {
            $level = 'low';
        }

        $breakdown[] = [
            'key'           => self::VULNERABILITY_KEY,
            'text'          => $vulnQuestion['text'],
            'answer_label'  => $vulnLabel,
            'weight'        => (int) $vulnQuestion['weight'],
            'points_scored' => $vulnBonus,
            'is_unsure'     => $vulnIsUnsure,
        ];

        return [
            'score'        => (int) $score,
            'level'        => $level,
            'unsure_ratio' => round($unsureRatio, 4),
            'breakdown'    => $breakdown,
        ];
    }

    private function buildQuestionSets(): array
    {
        return [
            'fire' => [
                'label' => 'Fire',
                'icon'  => 'flame',
                'questions' => [
                    [
                        'key'    => 'trapped',
                        'text'   => 'Is anyone trapped?',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'people_affected',
                        'text'   => 'Estimated number of people affected/trapped',
                        'weight' => 1,
                        'options' => [
                            ['label' => '0', 'points' => 0],
                            ['label' => '1-2', 'points' => 1],
                            ['label' => '3-5', 'points' => 2],
                            ['label' => '6+', 'points' => 3],
                        ],
                    ],
                    [
                        'key'    => 'spreading',
                        'text'   => 'Is fire spreading to adjacent structures?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 2],
                        ],
                    ],
                    [
                        'key'    => 'hazards',
                        'text'   => 'Are there LPG/chemical/electrical hazards?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 1],
                        ],
                    ],
                ],
            ],
            'flood' => [
                'label' => 'Flood',
                'icon'  => 'waves',
                'questions' => [
                    [
                        'key'    => 'water_level',
                        'text'   => 'Current water level',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'None', 'points' => 0],
                            ['label' => 'Ankle-deep', 'points' => 1],
                            ['label' => 'Knee-deep', 'points' => 2],
                            ['label' => 'Waist-deep', 'points' => 3],
                            ['label' => 'Neck-deep or higher', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'people_affected',
                        'text'   => 'Estimated number of people affected',
                        'weight' => 1,
                        'options' => [
                            ['label' => '0', 'points' => 0],
                            ['label' => '1-2', 'points' => 1],
                            ['label' => '3-5', 'points' => 2],
                            ['label' => '6+', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'rising',
                        'text'   => 'Is the water level still rising?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 2],
                        ],
                    ],
                ],
            ],
            'earthquake' => [
                'label' => 'Earthquake',
                'icon'  => 'activity',
                'questions' => [
                    [
                        'key'    => 'damage_level',
                        'text'   => 'Level of structural damage',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'None', 'points' => 0],
                            ['label' => 'Minor cracks', 'points' => 1],
                            ['label' => 'Moderate damage', 'points' => 2],
                            ['label' => 'Severe damage', 'points' => 3],
                            ['label' => 'Collapsed', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'people_trapped',
                        'text'   => 'Is anyone trapped under debris?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'aftershocks',
                        'text'   => 'Are aftershocks still occurring?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 2],
                        ],
                    ],
                ],
            ],
            'medical' => [
                'label' => 'Medical Emergency',
                'icon'  => 'heart-pulse',
                'questions' => [
                    [
                        'key'    => 'consciousness',
                        'text'   => 'Patient consciousness level',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'Conscious and alert', 'points' => 0],
                            ['label' => 'Drowsy/confused', 'points' => 2],
                            ['label' => 'Unconscious', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'bleeding',
                        'text'   => 'Severity of bleeding',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'None', 'points' => 0],
                            ['label' => 'Minor', 'points' => 1],
                            ['label' => 'Severe', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'breathing',
                        'text'   => 'Breathing status',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'Normal', 'points' => 0],
                            ['label' => 'Difficult', 'points' => 1],
                            ['label' => 'Not breathing', 'points' => 2],
                        ],
                    ],
                ],
            ],
            'accident' => [
                'label' => 'Accident',
                'icon'  => 'car',
                'questions' => [
                    [
                        'key'    => 'injuries',
                        'text'   => 'Severity of injuries',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'None', 'points' => 0],
                            ['label' => 'Minor', 'points' => 1],
                            ['label' => 'Serious', 'points' => 3],
                            ['label' => 'Fatal', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'vehicles_involved',
                        'text'   => 'Number of vehicles involved',
                        'weight' => 1,
                        'options' => [
                            ['label' => '1', 'points' => 0],
                            ['label' => '2', 'points' => 1],
                            ['label' => '3+', 'points' => 2],
                        ],
                    ],
                    [
                        'key'    => 'trapped',
                        'text'   => 'Is anyone trapped in a vehicle?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 4],
                        ],
                    ],
                ],
            ],
            'evacuation' => [
                'label' => 'Evacuation',
                'icon'  => 'users',
                'questions' => [
                    [
                        'key'    => 'urgency',
                        'text'   => 'How urgent is the evacuation?',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'Not needed', 'points' => 0],
                            ['label' => 'Soon', 'points' => 1],
                            ['label' => 'Immediate', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'people_count',
                        'text'   => 'Number of people needing evacuation',
                        'weight' => 1,
                        'options' => [
                            ['label' => '0', 'points' => 0],
                            ['label' => '1-5', 'points' => 1],
                            ['label' => '6-20', 'points' => 2],
                            ['label' => '21+', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'accessibility',
                        'text'   => 'Is the evacuation route accessible?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'Accessible', 'points' => 0],
                            ['label' => 'Partially blocked', 'points' => 1],
                            ['label' => 'Blocked', 'points' => 2],
                        ],
                    ],
                ],
            ],
            'security' => [
                'label' => 'Security',
                'icon'  => 'shield',
                'questions' => [
                    [
                        'key'    => 'threat_type',
                        'text'   => 'Type of security threat',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'None', 'points' => 0],
                            ['label' => 'Theft', 'points' => 1],
                            ['label' => 'Assault', 'points' => 3],
                            ['label' => 'Armed threat', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'in_progress',
                        'text'   => 'Is the threat still in progress?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'weapons',
                        'text'   => 'Are weapons involved?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'No', 'points' => 0],
                            ['label' => 'Yes', 'points' => 2],
                        ],
                    ],
                ],
            ],
            'other' => [
                'label' => 'Other',
                'icon'  => 'alert-circle',
                'questions' => [
                    [
                        'key'    => 'severity',
                        'text'   => 'Overall severity of the situation',
                        'weight' => 2,
                        'options' => [
                            ['label' => 'Minor', 'points' => 0],
                            ['label' => 'Moderate', 'points' => 2],
                            ['label' => 'Severe', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'people_affected',
                        'text'   => 'Estimated number of people affected',
                        'weight' => 1,
                        'options' => [
                            ['label' => '0', 'points' => 0],
                            ['label' => '1-2', 'points' => 1],
                            ['label' => '3-5', 'points' => 2],
                            ['label' => '6+', 'points' => 4],
                        ],
                    ],
                    [
                        'key'    => 'clarity',
                        'text'   => 'How clearly can you describe the situation?',
                        'weight' => 1,
                        'options' => [
                            ['label' => 'Clear', 'points' => 0],
                            ['label' => 'Vague', 'points' => 1],
                            ['label' => 'Unknown', 'points' => 2],
                        ],
                    ],
                ],
            ],
        ];
    }
}
