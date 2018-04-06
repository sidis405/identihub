<?php

namespace App\Http\Controllers\Api\V1;

use App\Font;
use App\Bridge;
use App\Section;
use App\FontFamily;
use App\FontVariant;
use App\SectionType;
use App\Jobs\CreateFont;
use App\Jobs\CreateSection;
use Illuminate\Http\Request;
use App\Events\BridgeUpdated;
use App\Jobs\CreateFontImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFontRequest;

class FontsController extends Controller
{
    public function search(Request $request, $search)
    {
        return response()->json([
            'fonts' => FontFamily::where('family', 'like', '%' . $search . '%')->with('variants')->get()
        ]);
    }

    public function store(StoreFontRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);

        $section = (new CreateSection($bridge, SectionType::where('name', 'FONTS')->first()))->handle();

        $font = (new CreateFont($request->get('font_variant_id'), $section))->handle();

        $section->title = $font->variant->fontFamily->family . ' ' . $font->variant->variant;
        $section->save();

        (new CreateFontImage(FontVariant::findOrFail($request->get('font_variant_id'))))->handle();

        event(new BridgeUpdated($bridge));

        $bridge = $bridge->loadCommonRelations();


        return response()->json([
                'bridge' => $bridge,
                'section_types' => SectionType::all()
            ]);
    }

    public function destroy(Bridge $bridge, Font $font)
    {
        $this->authorize('update', $bridge);

        $section = Section::findOrFail($font->section_id);
        $font->delete();
        $section->delete();

        $bridge = $bridge->loadCommonRelations();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge,
                'section_types' => SectionType::all()
            ]);
    }
}
