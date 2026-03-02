<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleResource;
use App\Models\Module;
use App\Models\Project;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function index(Project $project, Request $request)
    {
        $this->authorize('viewAny', Module::class);

        $modules = $project->modules()
            ->when($request->domain, fn($q, $d) => $q->where('domain', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->paginate(20);

        return ModuleResource::collection($modules);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('create', Module::class);

        $data = $this->validateModuleData($request);

        $module = $project->modules()->create($data);

        $this->auditService->logCreated('module', $module);

        return new ModuleResource($module);
    }

    public function show(Project $project, Module $module)
    {
        $this->authorize('view', $module);
        $this->ensureBelongsToProject($project, $module);

        // Attach validation status
        $module->validation_check = $module->validationCheck();

        return new ModuleResource($module);
    }

    public function update(Request $request, Project $project, Module $module)
    {
        $this->authorize('update', $module);
        $this->ensureBelongsToProject($project, $module);

        $before = $module->toArray();
        $data   = $this->validateModuleData($request, partial: true);

        $module->update($data);

        $this->auditService->logUpdated('module', $module, $before, $module->fresh()->toArray());

        $module->validation_check = $module->fresh()->validationCheck();

        return new ModuleResource($module->fresh());
    }

    public function validateModule(Project $project, Module $module)
    {
        $this->authorize('validate', $module);
        $this->ensureBelongsToProject($project, $module);

        // Server-side enforcement of validation rules (mandatory)
        $check = $module->validationCheck();

        if (!$check['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Module does not meet the requirements to be validated.',
                'errors'  => $check['errors'],
            ], 422);
        }

        $oldStatus = $module->status;
        $module->update(['status' => 'validated']);

        $this->auditService->logValidated('module', $module->id);
        $this->auditService->logStatusChanged('module', $module->id, $oldStatus, 'validated');

        return new ModuleResource($module->fresh());
    }

    public function destroy(Project $project, Module $module)
    {
        $this->authorize('delete', $module);
        $this->ensureBelongsToProject($project, $module);

        $module->delete();

        return response()->json(['success' => true, 'message' => 'Module deleted.']);
    }

    private function validateModuleData(Request $request, bool $partial = false): array
    {
        $rules = [
            'domain'                   => 'string|max:255',
            'name'                     => 'string|max:255',
            'status'                   => 'in:draft,validated,ready_for_build',
            'objective'                => 'nullable|string',
            'inputs'                   => 'nullable|array',
            'inputs.*'                 => 'string',
            'data_structure'           => 'nullable|string',
            'logic_rules'              => 'nullable|string',
            'outputs'                  => 'nullable|array',
            'outputs.*'                => 'string',
            'responsibility'           => 'nullable|string',
            'failure_scenarios'        => 'nullable|string',
            'audit_trail_requirements' => 'nullable|string',
            'dependencies'             => 'nullable|array',
            'dependencies.*'           => 'integer|exists:modules,id',
            'version_note'             => 'nullable|string|max:500',
        ];

        if (!$partial) {
            $rules['domain'] = 'required|' . $rules['domain'];
            $rules['name']   = 'required|' . $rules['name'];
        }

        return $request->validate(array_map(
            fn($rule) => $partial ? 'sometimes|' . $rule : $rule,
            $rules
        ));
    }

    private function ensureBelongsToProject(Project $project, Module $module): void
    {
        abort_if($module->project_id !== $project->id, 404);
    }
}
