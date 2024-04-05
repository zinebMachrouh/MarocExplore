<?php

namespace App\Http\Controllers;

use App\Http\Resources\RouteResource;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Else_;

/**
 * @OA\Tag(
 *     name="routes",
 *     description="Operations related to routes"
 * )
 */
class RouteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'search', 'filter']]);
    }
    /**
     * @OA\Get(
     *     path="/api/routes",
     *     operationId="getRoutes",
     *     tags={"routes"},
     *     summary="Get list of routes",
     *     description="Returns list of routes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index()
    {
        $routes = Route::with('destinations', 'category', 'user')->get();
        $categories = Category::all();
        $destinations = Destination::all();

        $responseData = [
            "routes" => RouteResource::collection($routes),
            "categories" => $categories,
            "destinations" => $destinations,
            "status" => 200,
        ];
        if (Auth::check()) {
            $user = Auth::user();
            $responseData['user'] = $user;
        }
        return response()->json($responseData);
    }
    /**
     * @OA\Post(
     *     path="/api/routes",
     *     operationId="storeRoute",
     *     tags={"routes"},
     *     summary="Create a new route",
     *     description="Create a new route with provided details",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "category_id", "duration", "picture", "destinations"},
     *             @OA\Property(property="title", type="string", example="Route Title"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="duration", type="integer", example=60),
     *             @OA\Property(property="picture", type="string", format="binary"),
     *             @OA\Property(property="destinations", type="string", example="1,2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Route created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Route created successfully"),
     *             @OA\Property(property="status", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'duration' => 'required|integer|min:0',
            'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'destinations' => 'required|array',
        ]);



        $url = $request->file('picture')->store('routes', 'public');
        $validatedData['picture'] = $url;
        $validatedData['user_id'] = Auth::user()->id;

        $route = Route::create($validatedData);

        $destinations = $request->input('destinations', []);
        foreach ($destinations as $destination) {
            if (Destination::find($destination)) {
                if (count($destinations) < 2) {
                    return response()->json([
                        'message' => 'At least two destinations are required',
                        'status' => 400
                    ], 400);
                } else {
                    $route->destinations()->attach($destination);
                }
            } else {
                return response()->json([
                    'message' => 'Invalid destination ID: ' . $destination,
                    'status' => 400
                ], 400);
            }
        }


        return response()->json([
            'message' => 'Route created successfully',
            'route' => $route,
            'status' => 201
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/routes/{route}",
     *     operationId="updateRoute",
     *     tags={"routes"},
     *     summary="Update an existing route",
     *     description="Update an existing route with provided details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="route",
     *         in="path",
     *         description="ID of the route to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "category_id", "duration", "picture"},
     *             @OA\Property(property="title", type="string", example="Updated Route Title"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="duration", type="integer", example=90),
     *             @OA\Property(property="picture", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Route updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Route updated successfully"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden Content"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function update(Request $request, Route $route)
    {

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'duration' => 'required|integer|min:0',
            'destinations' => 'required|array',
            'destinations.*' => 'exists:destinations,id',
        ]);

        if ($route->user_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'Forbidden Content',
                'status' => 403
            ]);
        }

        if ($request->hasFile('picture')) {
            $url = $request->file('picture')->store('routes', 'public');
            $validatedData['picture'] = $url;
        }

        $route->title = $validatedData['title'];
        $route->category_id = $validatedData['category_id'];
        $route->duration = $validatedData['duration'];

        $route->destinations()->sync($validatedData['destinations']);

        if ($route->save()) {
            return response()->json([
                'message' => 'Route updated successfully',
                'route' => $route,
                'status' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'Could not update route',
                'status' => 500
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/routes/{route}",
     *     operationId="deleteRoute",
     *     tags={"routes"},
     *     summary="Delete a route",
     *     description="Delete a route by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="route",
     *         in="path",
     *         description="ID of the route to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Route deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Route deleted successfully"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden Content"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function destroy(Route $route)
    {
        if ($route->user_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'Forbidden Content',
                'status' => 403
            ]);
        }
        $route->delete();

        return response()->json([
            'message' => 'Route deleted successfully',
            'status' => 200
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/routes/search/{category}",
     *     operationId="searchRoutesByCategory",
     *     tags={"routes"},
     *     summary="Search routes by category",
     *     description="Search routes by category name containing the specified substring",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Name of the category",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Routes found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Search By Category"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        $title = $validatedData['title'];
        $routes = Route::where('title', 'like', '%' . $title . '%')->with('destinations', 'category', 'user')->get();

        return response()->json([
            'message' => 'Search By Category',
            'routes' => $routes,
            'status' => 200
        ]);
    }
    public function filter(Request $request)
    {
        $validatedData = $request->validate([
            'category' => 'required|exists:categories,id',
        ]);
        $category = $validatedData['category'];
        $routes = Route::where('category_id', $category)->with('destinations', 'category')->get();;

        return response()->json([
            'message' => 'Search By Category',
            'routes' => $routes,
            'status' => 200
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/routes/{route}/watchlist",
     *     operationId="addToWatchlist",
     *     tags={"routes"},
     *     summary="Add route to watchlist",
     *     description="Add a route to the authenticated user's watchlist",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="route",
     *         in="path",
     *         description="ID of the route to add to watchlist",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Route added to watchlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Route added to the watchlist successfully"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Route is already in the watchlist"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function addToWatchlist(Route $route)
    {
        $user = User::find(Auth::user()->id);
        if ($user->wishlist()->where('route_id', $route->id)->exists()) {
            return response()->json([
                'message' => 'Route is already in the watchlist',
                'status' => 400
            ], 400);
        }

        $user->wishlist()->attach($route);

        return response()->json([
            'message' => 'Route added to the watchlist successfully',
            'status' => 200
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/destinations",
     *     operationId="createDestination",
     *     tags={"destinations"},
     *     summary="Create a new destination",
     *     description="Create a new destination with provided details",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "location", "recommendations"},
     *             @OA\Property(property="name", type="string", example="Destination Name"),
     *             @OA\Property(property="location", type="string", example="Destination Location"),
     *             @OA\Property(property="recommendations", type="string", example="Recommendation1,Recommendation2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Destination created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Destination created successfully"),
     *             @OA\Property(property="status", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function createDistination(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'recommendations' => 'required|string|max:255',
        ]);

        $destination = new Destination();
        $destination->name = $validatedData['name'];
        $destination->location = $validatedData['location'];
        $destination->recommendations =  json_encode(explode(',', $validatedData['recommendations']));

        $destination->save();

        return response()->json([
            'message' => 'Destination created successfully',
            'destination' => $destination,
            'status' => 201
        ], 201);
    }

    public function show(Route $route)
    {
        return response()->json([
            'route' => $route,
            'status' => 200
        ], 201);
    }
}
