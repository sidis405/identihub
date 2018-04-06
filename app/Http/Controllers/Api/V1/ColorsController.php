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
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateColorRequest;
use App\Http\Requests\CreateBulkColorRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ColorsController extends Controller
{
    public function storeBulkColors(CreateBulkColorRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);

        $sectionType = SectionType::where('name', SectionType::COLORS)->first();
        $section = Section::where('section_type_id', $sectionType->id)->where('bridge_id', $bridge->id)->first();

        (new CreateBulkColors($request->file('swatch'), $bridge->id, $section))->handle();

        $bridge = $bridge->loadCommonRelations();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge,
                'section_types' => SectionType::all()
            ]);
    }

    public function store(CreateColorRequest $request, $bridgeId)
    {
        try {
            $user = Auth::user();
            $bridge = Bridge::findOrFail($bridgeId);
            if ($user->id !== $bridge->user_id) {
                throw new ModelNotFoundException();
            }

            $sectionType = SectionType::where('name', SectionType::COLORS)->get()->first();
            $section = Section::where('section_type_id', $sectionType->id)->where('bridge_id', $bridgeId)->get()->first();

            (new CreateColor($request->only(['hex', 'cmyk', 'rgb']), $bridgeId, $section))->handle();

            $bridge = Bridge::with('sections', 'icons', 'icons.converted', 'images', 'images.converted', 'fonts', 'fonts.variant', 'fonts.variant.fontFamily', 'colors')->findOrFail($bridgeId);
            try {
                event(new BridgeUpdated($bridge));
            } catch (\Exception $e) {
            }
            return response()->json([
                'bridge' => $bridge,
                'section_types' => SectionType::all()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Entry not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error'
            ]);
        }
    }

    public function update(CreateColorRequest $request, $bridgeId, $colorId)
    {
        try {
            $user = Auth::user();
            $bridge = Bridge::findOrFail($bridgeId);
            if ($user->id !== $bridge->user_id) {
                throw new ModelNotFoundException();
            }

            (new UpdateColor($request->only(['hex', 'cmyk', 'rgb']), $bridgeId, Color::findOrFail($colorId)))->handle();

            $bridge = Bridge::with('sections', 'icons', 'icons.converted', 'images', 'images.converted', 'fonts', 'fonts.variant', 'fonts.variant.fontFamily', 'colors')->findOrFail($bridgeId);
            try {
                event(new BridgeUpdated($bridge));
            } catch (\Exception $e) {
            }
            return response()->json([
                'bridge' => $bridge,
                'section_types' => SectionType::all()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Entry not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error'
            ]);
        }
    }

    public function destroy($bridgeId, $colorId)
    {
        try {
            $user = Auth::user();
            $bridge = Bridge::findOrFail($bridgeId);
            if ($user->id !== $bridge->user_id) {
                throw new ModelNotFoundException();
            }

            $color = Color::findOrFail($colorId);
            $color->delete();

            $bridge = Bridge::with('sections', 'icons', 'icons.converted', 'images', 'images.converted', 'fonts', 'fonts.variant', 'fonts.variant.fontFamily', 'colors')->where('user_id', $user->id)->findOrFail($bridgeId);
            try {
                event(new BridgeUpdated($bridge));
            } catch (\Exception $e) {
            }
            return response()->json([
                'bridge' => $bridge,
                'section_types' => SectionType::all()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Entry not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error'
            ]);
        }
    }
}
