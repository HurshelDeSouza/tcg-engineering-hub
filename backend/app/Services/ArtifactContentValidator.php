<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service to validate content_json structure based on artifact type.
 * Each artifact type has specific required fields to ensure data quality.
 */
class ArtifactContentValidator
{
    /**
     * Validate content_json based on artifact type.
     * 
     * @throws ValidationException if validation fails
     */
    public function validate(string $type, ?array $content): void
    {
        if (empty($content)) {
            return; // Allow empty content for drafts
        }

        $rules = $this->getRulesForType($type);
        
        if (empty($rules)) {
            return; // No specific validation for this type
        }

        $validator = Validator::make($content, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'content_json' => $validator->errors()->all(),
            ]);
        }
    }

    /**
     * Get validation rules for each artifact type.
     */
    private function getRulesForType(string $type): array
    {
        return match($type) {
            'strategic_alignment' => [
                'transformation' => 'required|string|min:10',
                'supported_decisions' => 'required|array|min:1',
                'supported_decisions.*' => 'string',
                'measurable_success' => 'required|array|min:1',
                'measurable_success.*.metric' => 'required|string',
                'measurable_success.*.target' => 'required|string',
                'out_of_scope' => 'nullable|array',
                'out_of_scope.*' => 'string',
            ],

            'big_picture' => [
                'ecosystem_vision' => 'required|string|min:10',
                'impacted_domains' => 'required|array|min:1',
                'impacted_domains.*' => 'string',
                'success_definition' => 'required|string|min:10',
            ],

            'domain_breakdown' => [
                'domains' => 'required|array|min:1',
                'domains.*.name' => 'required|string',
                'domains.*.objective' => 'required|string',
                'domains.*.owner_user_id' => 'nullable|integer|exists:users,id',
            ],

            'module_matrix' => [
                'modules_overview' => 'required|array|min:1',
                'modules_overview.*.name' => 'required|string',
                'modules_overview.*.domain' => 'required|string',
                'modules_overview.*.priority' => 'nullable|string|in:high,medium,low',
                'modules_overview.*.phase' => 'nullable|string',
            ],

            'system_architecture' => [
                'auth_model' => 'required|string|min:5',
                'api_style' => 'required|string|min:3',
                'data_model_notes' => 'required|string|min:10',
                'scalability_notes' => 'nullable|string',
            ],

            'phase_scope' => [
                'included_modules' => 'required|array|min:1',
                'included_modules.*' => 'integer|exists:modules,id',
                'excluded_items' => 'nullable|array',
                'excluded_items.*' => 'string',
                'acceptance_criteria' => 'required|array|min:1',
                'acceptance_criteria.*' => 'string',
            ],

            'module_engineering' => [
                'modules' => 'required|array|min:1',
                'modules.*.module_id' => 'required|integer|exists:modules,id',
                'modules.*.engineering_approach' => 'required|string|min:10',
                'modules.*.technical_decisions' => 'required|array|min:1',
                'modules.*.technical_decisions.*' => 'string',
                'modules.*.implementation_notes' => 'nullable|string',
                'modules.*.risks' => 'nullable|array',
                'modules.*.risks.*' => 'string',
            ],

            default => [],
        };
    }

    /**
     * Get human-readable field requirements for a given artifact type.
     * Useful for showing users what fields are required.
     */
    public function getRequiredFields(string $type): array
    {
        return match($type) {
            'strategic_alignment' => [
                'transformation' => 'Description of the transformation (min 10 chars)',
                'supported_decisions' => 'Array of key decisions supported',
                'measurable_success' => 'Array of metrics with targets',
                'out_of_scope' => 'Optional: Items explicitly out of scope',
            ],

            'big_picture' => [
                'ecosystem_vision' => 'Vision of the ecosystem (min 10 chars)',
                'impacted_domains' => 'Array of impacted domains',
                'success_definition' => 'Definition of success (min 10 chars)',
            ],

            'domain_breakdown' => [
                'domains' => 'Array of domains with name, objective, and optional owner',
            ],

            'module_matrix' => [
                'modules_overview' => 'Array of modules with name, domain, priority, and phase',
            ],

            'system_architecture' => [
                'auth_model' => 'Authentication model description',
                'api_style' => 'API style (REST, GraphQL, etc.)',
                'data_model_notes' => 'Data model notes',
                'scalability_notes' => 'Optional: Scalability considerations',
            ],

            'phase_scope' => [
                'included_modules' => 'Array of module IDs included in this phase',
                'excluded_items' => 'Optional: Items excluded from this phase',
                'acceptance_criteria' => 'Array of acceptance criteria',
            ],

            'module_engineering' => [
                'modules' => 'Array of modules with engineering details',
                'modules.*.module_id' => 'Reference to existing module',
                'modules.*.engineering_approach' => 'Engineering approach description (min 10 chars)',
                'modules.*.technical_decisions' => 'Array of key technical decisions',
                'modules.*.implementation_notes' => 'Optional: Implementation notes',
                'modules.*.risks' => 'Optional: Array of identified risks',
            ],

            default => [],
        };
    }
}
