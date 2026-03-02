<?php

namespace App\Services;

use App\Models\Artifact;
use App\Models\Project;

/**
 * Domain Service que encapsula TODAS las reglas de negocio (Gates) del framework TCG.
 * Las reglas viven AQUÍ, nunca en controllers ni policies.
 */
class ArtifactGateService
{
    /**
     * N mínimo de módulos validados para completar system_architecture.
     * Configurable desde config/tcg.php
     */
    private int $minValidatedModules;

    public function __construct()
    {
        $this->minValidatedModules = config('tcg.min_validated_modules', 3);
    }

    /**
     * Verifica si un artifact puede ser marcado como "done".
     * Retorna ['allowed' => bool, 'reason' => string|null]
     */
    public function canMarkDone(Artifact $artifact): array
    {
        return match ($artifact->type) {
            'domain_breakdown'   => $this->checkGate1($artifact),
            'module_matrix'      => $this->checkGate2($artifact),
            'system_architecture' => $this->checkGate3($artifact),
            default              => ['allowed' => true, 'reason' => null],
        };
    }

    /**
     * Gate 1: domain_breakdown cannot be "done" if big_picture is not "done".
     */
    private function checkGate1(Artifact $artifact): array
    {
        $bigPicture = $artifact->project->getArtifactByType('big_picture');

        if (!$bigPicture || !$bigPicture->isDone()) {
            return [
                'allowed' => false,
                'reason'  => 'Gate 1: "Big Picture" must be completed before completing "Domain Breakdown".',
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Gate 2: module_matrix cannot be "done" if domain_breakdown is not "done".
     */
    private function checkGate2(Artifact $artifact): array
    {
        $domainBreakdown = $artifact->project->getArtifactByType('domain_breakdown');

        if (!$domainBreakdown || !$domainBreakdown->isDone()) {
            return [
                'allowed' => false,
                'reason'  => 'Gate 2: "Domain Breakdown" must be completed before completing "Module Matrix".',
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Gate 3: system_architecture requires at least N validated modules.
     */
    private function checkGate3(Artifact $artifact): array
    {
        $validatedCount = $artifact->project->modules()
            ->where('status', 'validated')
            ->count();

        if ($validatedCount < $this->minValidatedModules) {
            return [
                'allowed' => false,
                'reason'  => "Gate 3: At least {$this->minValidatedModules} validated modules are required. Currently there are {$validatedCount}.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Gate 4: Project cannot move from "discovery" → "execution" without key artifacts in "done".
     */
    public function canTransitionToExecution(Project $project): array
    {
        $required = [
            'strategic_alignment' => 'Strategic Alignment',
            'big_picture'         => 'Big Picture',
            'domain_breakdown'    => 'Domain Breakdown',
            'module_matrix'       => 'Module Matrix',
        ];

        $missing = [];

        foreach ($required as $type => $label) {
            $artifact = $project->getArtifactByType($type);
            if (!$artifact || !$artifact->isDone()) {
                $missing[] = $label;
            }
        }

        if (!empty($missing)) {
            $list = implode(', ', $missing);
            return [
                'allowed' => false,
                'reason'  => "Gate 4: The following artifacts must be completed to move to Execution: {$list}.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Obtiene el estado de bloqueo de un artifact (para mostrar en UI).
     */
    public function getBlockedReason(Artifact $artifact): ?string
    {
        // Only relevant for artifacts that have gate checks when targeting "done"
        if (!in_array($artifact->type, ['domain_breakdown', 'module_matrix', 'system_architecture'])) {
            return null;
        }

        $check = $this->canMarkDone($artifact);

        return $check['allowed'] ? null : $check['reason'];
    }
}
