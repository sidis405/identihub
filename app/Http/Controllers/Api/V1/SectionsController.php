<?php

namespace App\Http\Controllers\Api\V1;

use App\Bridge;
use App\Events\BridgeUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSectionRequest;
use App\Http\Requests\SectionDescriptionRequest;
use App\Http\Requests\SectionTitleRequest;
use App\Jobs\CreateSection;
use App\Section;
use App\SectionType;

class SectionsController extends Controller
{
    public function store(CreateSectionRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);
        (new CreateSection($bridge, SectionType::findOrFail($request->get('section_type'))))->handle();
        event(new BridgeUpdated($bridge));
        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }

    public function destroy(Bridge $bridge, Section $section)
    {
        $this->authorize('update', $bridge);
        $section->delete();
        event(new BridgeUpdated($bridge));
        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }

    public function updateTitle(SectionTitleRequest $request, Bridge $bridge, Section $section)
    {
        $this->authorize('update', $bridge);
        $section->title = $request->get('title');
        $section->save();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }

    public function updateDescription(SectionDescriptionRequest $request, Bridge $bridge, Section $section)
    {
        $this->authorize('update', $bridge);
        $section->description = $request->get('description');
        $section->save();
        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }
}
