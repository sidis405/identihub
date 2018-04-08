<?php

namespace App\Http\Controllers\Api\V1;

use App\Color;
use App\Bridge;
use App\Section;
use App\SectionType;
use App\Jobs\CreateColor;
use App\Jobs\UpdateColor;
use App\Events\BridgeUpdated;
use App\Jobs\CreateBulkColors;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateColorRequest;
use App\Http\Requests\CreateBulkColorRequest;

class ColorsController extends Controller
{
    public function storeBulkColors(CreateBulkColorRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);

        $sectionType = SectionType::where('name', SectionType::COLORS)->first();
        $section = Section::where('section_type_id', $sectionType->id)->where('bridge_id', $bridge->id)->first();

        (new CreateBulkColors($request->file('swatch'), $bridge->id, $section))->handle();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations(),
                'section_types' => SectionType::all()
            ]);
    }

    public function store(CreateColorRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);

        $sectionType = SectionType::where('name', SectionType::COLORS)->get()->first();
        $section = Section::where('section_type_id', $sectionType->id)->where('bridge_id', $bridge->id)->get()->first();

        (new CreateColor($request->only(['hex', 'cmyk', 'rgb']), $bridge->id, $section))->handle();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations(),
                'section_types' => SectionType::all()
            ]);
    }

    public function update(CreateColorRequest $request, Bridge $bridge, Color $color)
    {
        $this->authorize('update', $bridge);

        (new UpdateColor($request->only(['hex', 'cmyk', 'rgb']), $bridge->id, $color))->handle();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations(),
                'section_types' => SectionType::all()
            ]);
    }

    public function destroy(Bridge $bridge, Color $color)
    {
        $this->authorize('update', $bridge);

        $color->delete();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations(),
                'section_types' => SectionType::all()
            ]);
    }
}
