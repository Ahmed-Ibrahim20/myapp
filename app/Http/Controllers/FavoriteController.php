<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteRequest;
use App\Services\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FavoriteController extends Controller
{
    protected FavoriteService $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * Display a listing of the user's favorites.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $favorites = $this->favoriteService->indexFavorites($perPage);
        return response()->json($favorites);
    }

    /**
     * Store a newly created favorite in storage.
     */
    public function store(FavoriteRequest $request)
    {
        $result = $this->favoriteService->storeFavorite($request->validated());
        if ($result['status']) {
            return response()->json($result, Response::HTTP_CREATED);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified favorite.
     */
    public function show($id)
    {
        $favorite = $this->favoriteService->showFavorite($id);
        if ($favorite) {
            return response()->json($favorite);
        }
        return response()->json(['status' => false, 'message' => 'Favorite not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Remove the specified favorite from storage.
     */
    public function destroy($id)
    {
        $result = $this->favoriteService->destroyFavorite($id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update the specified favorite in storage.
     */
    public function update(FavoriteRequest $request, $id)
    {
        $result = $this->favoriteService->updateFavorite($request->validated(), $id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }
}