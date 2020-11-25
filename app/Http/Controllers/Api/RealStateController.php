<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessage;
use App\Http\Requests\RealStateRequest;
use App\RealState;
use App\Http\Controllers\Controller;

class RealStateController extends Controller
{
    private $realState;

    public function __construct(RealState $realState)
    {
        $this->realState = $realState;
    }

    public function index()
    {
        $realState = auth('api')->user()->real_state();

        return response()->json($realState->paginate(10), 200);
    }

    public function show($id)
    {
        try {
            $realState = auth('api')->user()->real_state()->with('photos')->findOrFail($id);

            return response()->json([
                'data' => $realState
            ]);
        } catch (\Exception $e) {
            $apiMessage = new ApiMessage($e->getMessage());
            return response()->json($apiMessage->getMessage(), 401);
        }
    }

    public function store(RealStateRequest $request)
    {
        $data = $request->all();
        $images = $request->file('images');

        try {
            $data['user_id'] = auth('api')->user()->id;

            $realState = $this->realState->create($data);

            if (isset($data['categories']) && count($data['categories'])) {
                $realState->categories()->sync($data['categories']);
            }

            if ($images) {
                $imagesUploaded = [];

                foreach ($images as $image) {
                    $path = $image->store('images', 'public');
                    $imagesUploaded[] = ['photo' => $path, 'is_thumb' => false];
                }

                $realState->photos()->createMany($imagesUploaded);
            }

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel cadastrado com sucesso.'
                ]
            ], 200);
        } catch (\Exception $e) {
            $apiMessage = new ApiMessage($e->getMessage());
            return response()->json($apiMessage->getMessage(), 401);
        }

    }

    public function update(RealStateRequest $request, $id)
    {
        $data = $request->all();
        $images = $request->file('images');

        try {
            $realState = auth('api')->user()->real_state()->findOrFail($id);
            $realState->update($data);

            if (isset($data['categories']) && count($data['categories'])) {
                $realState->categories()->sync($data['categories']);
            }

            if ($images) {
                $imagesUploaded = [];

                foreach ($images as $image) {
                    $path = $image->store('images', 'public');
                    $imagesUploaded[] = ['photo' => $path, 'is_thumb' => false];
                }

                $realState->photos()->createMany($imagesUploaded);
            }

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel atualizado com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            $apiMessage = new ApiMessage($e->getMessage());
            return response()->json($apiMessage->getMessage(), 401);
        }
    }

    public function destroy($id)
    {
        try {
            $realState = auth('api')->user()->real_state()->findOrFail($id);
            $realState->delete();

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel removido com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            $apiMessage = new ApiMessage($e->getMessage());
            return response()->json($apiMessage->getMessage(), 401);
        }
    }
}
