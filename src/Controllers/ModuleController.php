<?php

namespace LucasKaiut\Okrestri\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use LucasKaiut\Orkestri\Models\Module;
use LucasKaiut\Orkestri\Services\ModuleService;
use LucasKaiut\Orkestri\Requests\ModuleRequest;

class ModuleController extends Controller
{
    protected ModuleService $service;

    public function __construct(ModuleService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $modules = $this->service->paginate();

        return view('orkestri::pages.modules', compact('modules'));
    }

    public function create()
    {
        $modules = $this->service->all(['*'], ['fields']);
        return view('orkestri::pages.create-module', compact('modules'));
    }

    public function edit(Module $module)
    {
        $module->load('fields');
        $modules = $this->service->all(['*'], ['fields']);
        return view('orkestri::pages.edit-module', compact('module', 'modules'));
    }

    public function store(ModuleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $this->service->create($request->all());
            return redirect()->route('modules.index');
        });
    }

    public function update(ModuleRequest $request, $id)
    {
        $this->service->update($id, $request->all());
        return redirect()->route('modules.index');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return redirect()->route('modules.index');
    }

    public function runMigration(Module $module)
    {
        $this->service->runMigration($module);
        return redirect()->route('modules.index');
    }
}