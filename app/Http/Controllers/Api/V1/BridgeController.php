<?php

namespace App\Http\Controllers\Api\V1;

use App\Bridge;
use App\SectionType;
use App\Events\BridgeCreated;
use App\Events\BridgeDeleted;
use App\Events\BridgeUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBridgeSlugRequest;
use App\Http\Requests\BridgeStoreRequest;
use App\Http\Requests\BridgeUpdateRequest;

class BridgeController extends Controller
{
    public function index()
    {
        return response()->json([
            'bridges' => Bridge::with(
                'sections',
                'icons.converted',
                'images.converted',
                'fonts.variant.fontFamily',
                'colors'
            )->where('user_id', auth()->id())->get(),
            'section_types' => SectionType::all()
        ]);
    }

    public function show(Bridge $bridge)
    {
        $this->authorize('view', $bridge);

        return response()->json([
                'bridge' => $bridge->loadCommonRelations(),
                'section_types' => SectionType::all()
            ]);
    }

    /**
     * Description
     * @param App\Http\Requests\BridgeStoreRequest $request
     * @return Illuminate\Http\Response
     */
    public function store(BridgeStoreRequest $request)
    {
        $name = $request->get('name');

        $bridgeData = [
            'name' => $name,
            'user_id' => auth()->id(),
            'slug' => $this->getSlug(str_slug($name))
        ];

        $bridge = Bridge::create($bridgeData);

        event(new BridgeCreated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }

    public function update(BridgeUpdateRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);

        $bridge->name = request('name');
        $bridge->save();

        event(new BridgeUpdated($bridge->fresh()));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }

    public function updateSlug(UpdateBridgeSlugRequest $request, Bridge $bridge)
    {
        $this->authorize('update', $bridge);

        $slug = $this->getSlug($request->get('slug'));

        $bridge->slug = $slug;
        $bridge->save();

        event(new BridgeUpdated($bridge));

        return response()->json([
                'bridge' => $bridge->loadCommonRelations()
            ]);
    }

    private function getSlug($slugStr)
    {
        $slugs = Bridge::where('slug', $slugStr)->get();
        if ($slugs->count() !== 0) {
            return $slugStr . '-' . substr(md5(microtime()), rand(0, 26), 5);
        }
        return $slugStr;
    }

    public function destroy(Bridge $bridge)
    {
        $this->authorize('delete', $bridge);

        $bridge->delete();

        event(new BridgeDeleted($bridge));

        return response()->json([
                'bridges' => Bridge::with(
                    'sections',
                    'icons.converted',
                    'images.converted',
                    'fonts.variant.fontFamily',
                    'colors'
                )->where('user_id', auth()->id())->get(),
                'section_types' => SectionType::all()
            ]);
    }
}
