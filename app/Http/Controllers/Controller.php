<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 * title="Maroc Explore API",
 * description="The main objective of this project is to develop a robust API for route management, allowing authenticated users to create custom routes consisting of a title, category (beach, mountain, river, monument, etc.). ), a duration, an image and 2 or more destinations. Each destination will be characterized by its name, a place to stay and a list of places to visit/activities/food to try.",
 * version="1.0.0",
 * )
 * @OA\SecurityScheme(
 * type="http",
 * securityScheme="bearerAuth",
 * scheme="bearer",
 * bearerFormat="JWT"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
