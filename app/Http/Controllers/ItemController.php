<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index','show');
        $this->middleware('role:admin')->except('index','show');
    }


    /**
     * @OA\get(
     * path="/items",
     * tags={"User - Items"},
     * @OA\Parameter(
     *    in="query",
     *    name="type",
     *    required=false,
     *    @OA\Schema(type="string", enum={"slider","not_slider","all"}),
     * ),
     * description="get all items",
     * operationId="getItems",
     * @OA\Response(
     *     response=200,
     *     description="successful operation",
     *  ),
     *  )
     */
    public function index(Request $request)
    {
        $request->validate([
            'type'   => ['string', 'in:slider,not_slider,all'],
        ]);
        $q = Item::query();

        if($request->type == 'slider')
            $q->where('is_slider_item', true);
        if($request->type == 'not_slider')
            $q->where('is_slider_item', false);

        $items = $q->get();
        return ItemResource::collection($items);
    }

    /**
     * @OA\post(
     * path="/items",
     * tags={"User - Items"},
     * description="Add new item",
     * operationId="addItem",
     * @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"image"},
     *              @OA\Property(property="image", type="file"),
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="is_slider_item", type="integer", enum={0,1}),
     *           )
     *       )
     *),
     * @OA\Response(
     *     response=200,
     *     description="successful operation",
     *  ),
     * security={{"bearer_token":{}}}
     *)
     */
    public function store(Request $request)
    {
        $request->validate([
            'image'             => ['required','image'],
            'is_slider_item'    => ['required','boolean'],
            'title'             => ['nullable', 'string'],
        ]);

        if ($request->file('image')) {
            $imagePath = upload_file($request->image, 'item', 'items');
        } else {
            return response()->json(['message' => 'Invalid image upload.'], 400);
        }

        $item = Item::create([
            'image'             =>  $imagePath,
            'title'             =>  $request->title,
            'is_slider_item'    =>  $request->is_slider_item,
        ]);

        return response()->json(new ItemResource($item), 201);
    }

    /**
    * @OA\get(
    * path="/items/{id}",
    * description="Retrieve a specific item",
    * operationId="retrieveItem",
    * tags={"User - Items"},
    * @OA\Parameter(
    *      in="path",
    *      name="id",
    *      required=true,
    *      @OA\Schema(type="string"),
    * ),
    *
    * @OA\Response(
    *     response=200,
    *     description="successful operation",
    *  ),
    *  )
    */
    public function show(Item $item)
    {
        return response()->json(new ItemResource($item), 200);
    }

     /**
     * @OA\post(
     * path="/items/{id}",
     * description="Update a specific item",
     * operationId="updateItem",
     * tags={"User - Items"},
     * @OA\Parameter(
     *    in="path",
     *    name="id",
     *    required=true,
     *    @OA\Schema(type="string"),
     * ),
     *@OA\RequestBody(
     *  required = true,
     *  @OA\MediaType(
     *      mediaType="multipart/form-data",
     *      @OA\Schema(
     *          required = {"image"},
     *          @OA\Property(property="image", type="file"),
     *          @OA\Property(property="_method", type="string",format="string", example="PUT"),
     *      )
     *  )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="successful operation",
     *  ),
     * security={{"bearer_token":{}}}
     *)
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'image'               => ['required'],
            'title'               => ['nullable', 'string'],
            'is_slider_item'      => ['required', 'boolean'],
        ]);

        if(($request->image == $item->image)){
            $imagePath = $request->image;
        } elseif ($request->file('image')->isValid()){
            delete_file_if_exist($item->image);
            $imagePath = upload_file($request->image, 'item', 'items');
        } else {
            return response()->json(['message' => 'Invalid image upload.'], 400);
        }

        $item->image = $imagePath;
        $item->title = $request->title;
        $item->is_slider_item = $request->is_slider_item;

        $item->save();

        return response()->json(new ItemResource($item), 200);
    }

    /**
     * @OA\delete(
     * path="/items/{id}",
     * description="Delete a specific item",
     * operationId="deleteItem",
     * tags={"User - Items"},
     * @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string"),
     * ),
     * @OA\Response(
     *     response=200,
     *     description="successful operation",
     *  ),
     * security={{"bearer_token":{}}}
     *  )
     */
    public function destroy(Item $item)
    {
        delete_file_if_exist($item->image);
        $item->delete();
        return response()->json(null, 204);
    }
}
